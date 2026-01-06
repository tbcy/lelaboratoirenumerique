<?php

namespace App\Services\Social;

use App\Models\SocialConnection;
use App\Models\SocialPost;
use Exception;
use Illuminate\Support\Facades\Storage;
use Noweh\TwitterApi\Client;

class TwitterConnector
{
    protected Client $client;

    protected SocialConnection $connection;

    public function __construct(SocialConnection $connection)
    {
        if ($connection->platform !== 'twitter') {
            throw new Exception(__('resources.social_connection.errors.twitter_wrong_platform'));
        }

        if (! $connection->is_active) {
            throw new Exception(__('resources.social_connection.errors.twitter_disabled'));
        }

        $this->connection = $connection;
        $this->initializeClient();
    }

    protected function initializeClient(): void
    {
        $credentials = $this->connection->credentials;

        $this->client = new Client([
            'account_id' => '', // Not used for now
            'consumer_key' => $credentials['api_key'],
            'consumer_secret' => $credentials['api_secret'],
            'bearer_token' => '', // OAuth 1.0a does not use bearer token
            'access_token' => $credentials['access_token'],
            'access_token_secret' => $credentials['access_token_secret'],
        ]);
    }

    /**
     * Publish a tweet
     */
    public function publishTweet(SocialPost $post): array
    {
        try {
            $tweetData = ['text' => $post->content];

            // Upload images if present
            if (! empty($post->images)) {
                $mediaIds = $this->uploadImages($post->images);
                if (! empty($mediaIds)) {
                    $tweetData['media'] = ['media_ids' => $mediaIds];
                }
            }

            // Publish the tweet
            $response = $this->client->tweet()->create()->performRequest($tweetData);

            // Update connection
            $this->connection->updateLastUsed();

            return [
                'success' => true,
                'tweet_id' => $response->data->id ?? null,
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
     * Upload images to X
     */
    protected function uploadImages(array $imagePaths): array
    {
        $mediaIds = [];

        foreach ($imagePaths as $imagePath) {
            try {
                // Get file from private storage (local)
                $imageContent = Storage::disk('local')->get($imagePath);

                // Base64 encode the image content
                $base64Data = base64_encode($imageContent);

                // Upload via API v1.1 (media upload)
                $response = $this->client->uploadMedia()->upload($base64Data);

                // Extract media_id_string from array response
                if (isset($response['media_id_string'])) {
                    $mediaIds[] = $response['media_id_string'];
                }
            } catch (Exception $e) {
                // Log error but continue with other images
                logger()->error('X/Twitter image upload error', [
                    'image' => $imagePath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $mediaIds;
    }

    /**
     * Verify credentials validity
     */
    public function verifyCredentials(): bool
    {
        try {
            $response = $this->client->userMeLookup()->performRequest();

            return isset($response->data);
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
            $response = $this->client->userMeLookup()->performRequest();

            return (array) ($response->data ?? null);
        } catch (Exception $e) {
            return null;
        }
    }
}
