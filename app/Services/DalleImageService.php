<?php

namespace App\Services;

use App\Models\Company;
use App\Models\GeneratedImage;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DalleImageService
{
    protected string $apiKey;
    protected string $systemPrompt;
    protected string $baseUrl = 'https://api.openai.com/v1/images/generations';
    protected string $model = 'gpt-image-1.5';
    protected string $size = '1024x1024';
    protected string $quality = 'high';

    /**
     * Default system prompt for image generation.
     * This prompt ensures images match the "Le Laboratoire Numerique" brand identity.
     */
    public const DEFAULT_SYSTEM_PROMPT = <<<'PROMPT'
À partir du texte fourni, utiliser uniquement les idées et le message de fond comme source
d'inspiration, sans jamais reprendre ni afficher le texte tel quel.

Imaginer une illustration ou une infographie qui traduit visuellement le concept,
à l'aide de symboles, métaphores visuelles, icônes ou formes graphiques,
plutôt que du texte explicatif.

Respecter la charte graphique du site "Le Laboratoire Numérique" :
- Style moderne, épuré et digital
- Fond sombre (bleu foncé / presque noir)
- Éléments clairs (blanc) pour la lisibilité
- Une couleur d'accent vive pour les points clés
- Esthétique technologique avec motifs abstraits, grilles, lignes ou éléments géométriques
- Typographie sans serif, moderne et discrète (si du texte est nécessaire, le reformuler et le limiter)

Ce visuel est destiné à accompagner un post sur les réseaux sociaux (LinkedIn, X/Twitter, Instagram) :
- Impact visuel immédiat
- Hiérarchie claire
- Lisible rapidement, y compris sur mobile
- Format carré ou vertical adapté aux réseaux sociaux
- Pas de blocs de texte longs, privilégier le visuel

Produire un visuel final propre, professionnel, en haute résolution,
adapté à une communication tech et digitale sur les réseaux sociaux.
PROMPT;

    public function __construct()
    {
        $company = Company::first();
        $this->apiKey = $company?->openai_api_key ?? '';
        $this->systemPrompt = $company?->image_generation_prompt ?? self::DEFAULT_SYSTEM_PROMPT;
    }

    /**
     * Check if the service is properly configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Generate an image from a text prompt.
     *
     * @param  string  $contentText  The content to base the image on
     * @param  SocialPost|null  $socialPost  Optional social post to link the image to
     * @return array{success: bool, image?: GeneratedImage, error?: string}
     */
    public function generate(string $contentText, ?SocialPost $socialPost = null): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'OpenAI API key is not configured. Please set it in Settings.',
            ];
        }

        $fullPrompt = $this->systemPrompt . "\n\n---\n\nContent to visualize:\n" . $contentText;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(180)->post($this->baseUrl, [
                'model' => $this->model,
                'prompt' => $fullPrompt,
                'n' => 1,
                'size' => $this->size,
                'quality' => $this->quality,
            ]);

            if (! $response->successful()) {
                $error = $response->json('error.message') ?? $response->body();

                return [
                    'success' => false,
                    'error' => "OpenAI API error: {$error}",
                ];
            }

            $data = $response->json();

            // GPT Image models return base64 directly, DALL-E returns URL
            $imageBase64 = $data['data'][0]['b64_json'] ?? null;
            $imageUrl = $data['data'][0]['url'] ?? null;

            if ($imageBase64) {
                // GPT Image model response (base64)
                $imageData = base64_decode($imageBase64);
            } elseif ($imageUrl) {
                // DALL-E model response (URL) - fallback
                $imageData = $this->downloadImage($imageUrl);
            } else {
                return [
                    'success' => false,
                    'error' => 'No image data was returned in the response.',
                ];
            }

            if (! $imageData) {
                return [
                    'success' => false,
                    'error' => 'Failed to process the generated image.',
                ];
            }

            // Save the image
            $fileName = 'gpt_image_' . Str::uuid() . '.png';
            $filePath = 'generated-images/' . $fileName;

            Storage::disk('local')->put($filePath, $imageData);

            $fileSize = Storage::disk('local')->size($filePath);

            // Create database record
            $generatedImage = GeneratedImage::create([
                'prompt' => $fullPrompt,
                'content_source' => $contentText,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'mime_type' => 'image/png',
                'file_size' => $fileSize,
                'social_post_id' => $socialPost?->id,
                'api_response' => $data,
            ]);

            return [
                'success' => true,
                'image' => $generatedImage,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to generate image: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate an image and add it to a social post.
     *
     * @return array{success: bool, image?: GeneratedImage, error?: string}
     */
    public function generateForSocialPost(SocialPost $socialPost): array
    {
        $result = $this->generate($socialPost->content, $socialPost);

        if ($result['success'] && isset($result['image'])) {
            // Copy image to social-posts directory
            $socialPostPath = 'social-posts/' . $result['image']->file_name;
            Storage::disk('local')->copy($result['image']->file_path, $socialPostPath);

            // Add to social post images array
            $images = $socialPost->images ?? [];
            $images[] = $socialPostPath;
            $socialPost->update(['images' => $images]);

            // Update the generated image with social post reference
            $result['image']->update(['social_post_id' => $socialPost->id]);
        }

        return $result;
    }

    /**
     * Download image from URL.
     *
     * @return string|null Binary image data
     */
    protected function downloadImage(string $url): ?string
    {
        try {
            $response = Http::timeout(60)->get($url);

            if ($response->successful()) {
                return $response->body();
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the current system prompt.
     */
    public function getSystemPrompt(): string
    {
        return $this->systemPrompt;
    }

    /**
     * Get the default system prompt.
     */
    public static function getDefaultSystemPrompt(): string
    {
        return self::DEFAULT_SYSTEM_PROMPT;
    }
}
