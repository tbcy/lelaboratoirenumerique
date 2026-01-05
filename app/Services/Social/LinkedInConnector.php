<?php

namespace App\Services\Social;

use App\Models\SocialConnection;
use App\Models\SocialPost;
use LinkedIn\Client;
use LinkedIn\AccessToken;
use Exception;
use Illuminate\Support\Facades\Storage;

class LinkedInConnector
{
    protected Client $client;
    protected SocialConnection $connection;

    public function __construct(SocialConnection $connection)
    {
        if ($connection->platform !== 'linkedin') {
            throw new Exception(__('Cette connexion n\'est pas une connexion LinkedIn'));
        }

        if (!$connection->is_active) {
            throw new Exception(__('Cette connexion LinkedIn est désactivée'));
        }

        $this->connection = $connection;
        $this->initializeClient();
    }

    protected function initializeClient(): void
    {
        $credentials = $this->connection->credentials;

        $this->client = new Client(
            $credentials['client_id'],
            $credentials['client_secret']
        );

        // Configure access token if available
        if (!empty($credentials['access_token'])) {
            // Check if token is expired and refresh if needed
            if ($this->isTokenExpired() && !empty($credentials['refresh_token'])) {
                $this->refreshAccessToken();
                $credentials = $this->connection->fresh()->credentials;
            }

            // Create AccessToken object
            $accessToken = new AccessToken(
                $credentials['access_token'],
                $credentials['expires_at'] ?? time() + 5184000 // 60 days default
            );
            $this->client->setAccessToken($accessToken);
        }
    }

    /**
     * Check if the access token is expired or about to expire (within 5 minutes)
     */
    protected function isTokenExpired(): bool
    {
        $credentials = $this->connection->credentials;

        if (empty($credentials['expires_at'])) {
            return false; // No expiration date, assume not expired
        }

        // Consider expired if expiring within 5 minutes
        return $credentials['expires_at'] <= (time() + 300);
    }

    /**
     * Refresh the access token using the refresh token
     */
    protected function refreshAccessToken(): bool
    {
        try {
            $credentials = $this->connection->credentials;

            if (empty($credentials['refresh_token'])) {
                logger()->warning('LinkedIn: Pas de refresh token disponible', [
                    'connection_id' => $this->connection->id,
                ]);
                return false;
            }

            // Use Guzzle to call LinkedIn token endpoint
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://www.linkedin.com/oauth/v2/accessToken', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $credentials['refresh_token'],
                    'client_id' => $credentials['client_id'],
                    'client_secret' => $credentials['client_secret'],
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (!isset($data['access_token'])) {
                logger()->error('LinkedIn: Échec du renouvellement du token', [
                    'connection_id' => $this->connection->id,
                    'response' => $data,
                ]);
                return false;
            }

            // Update credentials with new token
            $newCredentials = $credentials;
            $newCredentials['access_token'] = $data['access_token'];
            $newCredentials['expires_at'] = time() + ($data['expires_in'] ?? 5184000); // Default 60 days

            // Update refresh token if provided
            if (isset($data['refresh_token'])) {
                $newCredentials['refresh_token'] = $data['refresh_token'];
            }

            $this->connection->update([
                'credentials' => $newCredentials,
            ]);

            logger()->info('LinkedIn: Token renouvelé avec succès', [
                'connection_id' => $this->connection->id,
                'expires_at' => date('Y-m-d H:i:s', $newCredentials['expires_at']),
            ]);

            return true;
        } catch (Exception $e) {
            logger()->error('LinkedIn: Erreur lors du renouvellement du token', [
                'connection_id' => $this->connection->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Publish a post on LinkedIn
     */
    public function publishPost(SocialPost $post): array
    {
        try {
            // Verify we have an access token
            if (empty($this->connection->credentials['access_token'])) {
                throw new Exception(__('Aucun access token configuré pour cette connexion LinkedIn'));
            }

            // Get person URN (profile identifier)
            // Use userinfo endpoint (OpenID Connect)
            $profile = $this->client->get('userinfo');

            if (!isset($profile['sub'])) {
                throw new Exception(__('Impossible de récupérer le profil LinkedIn'));
            }

            $authorUrn = 'urn:li:person:' . $profile['sub'];

            // Prepare post data
            $postData = [
                'author' => $authorUrn,
                'lifecycleState' => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => [
                            'text' => $post->content
                        ],
                        'shareMediaCategory' => 'NONE'
                    ]
                ],
                'visibility' => [
                    'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'
                ]
            ];

            // Handle images if present
            if (!empty($post->images)) {
                $mediaUrns = $this->uploadImages($post->images, $authorUrn);

                if (!empty($mediaUrns)) {
                    $postData['specificContent']['com.linkedin.ugc.ShareContent']['shareMediaCategory'] = 'IMAGE';
                    $postData['specificContent']['com.linkedin.ugc.ShareContent']['media'] = $mediaUrns;
                }
            }

            // Publish post via UGC API (User Generated Content)
            $response = $this->client->post('ugcPosts', $postData);

            // Update connection
            $this->connection->updateLastUsed();

            return [
                'success' => true,
                'post_id' => $response['id'] ?? null,
                'response' => $response,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Upload images to LinkedIn
     */
    protected function uploadImages(array $imagePaths, string $authorUrn): array
    {
        $mediaUrns = [];

        foreach ($imagePaths as $imagePath) {
            try {
                // Step 1: Register upload
                $registerUpload = $this->client->post('assets?action=registerUpload', [
                    'registerUploadRequest' => [
                        'recipes' => ['urn:li:digitalmediaRecipe:feedshare-image'],
                        'owner' => $authorUrn,
                        'serviceRelationships' => [
                            [
                                'relationshipType' => 'OWNER',
                                'identifier' => 'urn:li:userGeneratedContent'
                            ]
                        ]
                    ]
                ]);

                if (!isset($registerUpload['value']['asset'])) {
                    continue;
                }

                $asset = $registerUpload['value']['asset'];
                $uploadUrl = $registerUpload['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];

                // Step 2: Upload image via direct binary upload
                $imageContent = Storage::disk('local')->get($imagePath);

                // Use cURL for binary upload
                $ch = curl_init($uploadUrl);
                curl_setopt_array($ch, [
                    CURLOPT_CUSTOMREQUEST => 'PUT',
                    CURLOPT_POSTFIELDS => $imageContent,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $this->connection->credentials['access_token'],
                        'Content-Type: application/octet-stream'
                    ]
                ]);

                $uploadResponse = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode >= 200 && $httpCode < 300) {
                    $mediaUrns[] = [
                        'status' => 'READY',
                        'description' => [
                            'text' => ''
                        ],
                        'media' => $asset,
                        'title' => [
                            'text' => ''
                        ]
                    ];
                }
            } catch (Exception $e) {
                // Log error but continue with other images
                logger()->error('Erreur upload image LinkedIn', [
                    'image' => $imagePath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $mediaUrns;
    }

    /**
     * Verify credentials validity
     */
    public function verifyCredentials(): bool
    {
        try {
            if (empty($this->connection->credentials['access_token'])) {
                return false;
            }

            $profile = $this->client->get('userinfo');
            return isset($profile['sub']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get account information
     */
    public function getAccountInfo(): ?array
    {
        try {
            if (empty($this->connection->credentials['access_token'])) {
                return null;
            }

            $profile = $this->client->get('userinfo');
            return $profile ?? null;
        } catch (Exception $e) {
            return null;
        }
    }
}
