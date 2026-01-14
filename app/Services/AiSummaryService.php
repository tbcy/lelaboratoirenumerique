<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSummaryService
{
    protected ?string $apiKey = null;
    protected string $model = 'gpt-4o';
    protected string $detailLevel = 'concise';
    protected string $baseUrl = 'https://api.openai.com/v1/chat/completions';

    public const SHORT_SUMMARY_PROMPT = <<<'PROMPT'
Tu es un assistant qui génère des résumés concis.
Génère un résumé en 3 lignes maximum du contenu suivant.
Le résumé doit être factuel et capturer les points essentiels.
Format: paragraphe simple en HTML (<p>...</p>), pas de bullet points.
Langue: Français.
PROMPT;

    public const LONG_SUMMARY_CONCISE_PROMPT = <<<'PROMPT'
Tu es un assistant qui rédige des comptes-rendus de réunion.
Génère un compte-rendu structuré à partir du contenu suivant.

Format attendu (HTML):
<h3>Contexte</h3>
<p>[Brève mise en contexte]</p>

<h3>Points abordés</h3>
<ul>
  <li>[Point 1]</li>
  <li>[Point 2]</li>
</ul>

<h3>Décisions prises</h3>
<ul>
  <li>[Décision 1]</li>
</ul>

<h3>Actions à suivre</h3>
<ul>
  <li>[Action 1 - Responsable si mentionné]</li>
</ul>

<h3>Prochaines étapes</h3>
<p>[Ce qui est prévu ensuite]</p>

Note: Adapte les sections selon le contenu disponible.
Si une section n'est pas pertinente, omets-la.
Langue: Français.
PROMPT;

    public const LONG_SUMMARY_EXHAUSTIVE_PROMPT = <<<'PROMPT'
Tu es un assistant expert en rédaction de comptes-rendus détaillés.
Génère un compte-rendu EXHAUSTIF et COMPLET à partir du contenu suivant.

IMPORTANT: Ne résume pas, sois exhaustif. Inclus TOUS les détails, exemples, chiffres et informations mentionnés.

Format attendu (HTML):
<h3>Contexte et participants</h3>
<p>[Contexte détaillé de la réunion/échange, participants si mentionnés]</p>

<h3>Sujets abordés en détail</h3>
[Pour chaque sujet majeur, créer une sous-section:]
<h4>[Sujet 1]</h4>
<p>[Description complète avec tous les détails, arguments, exemples mentionnés]</p>
<ul>
  <li>[Points spécifiques avec détails]</li>
</ul>

<h4>[Sujet 2]</h4>
<p>[...]</p>

<h3>Discussions et échanges</h3>
<p>[Résumé des discussions, questions posées, réponses apportées, points de vue exprimés]</p>

<h3>Chiffres et données clés</h3>
<ul>
  <li>[Tout chiffre, statistique, montant, date, délai mentionné]</li>
</ul>

<h3>Décisions prises</h3>
<ul>
  <li>[Chaque décision avec son contexte et justification si mentionnée]</li>
</ul>

<h3>Actions à suivre</h3>
<table>
  <tr><th>Action</th><th>Responsable</th><th>Échéance</th></tr>
  <tr><td>[Action détaillée]</td><td>[Nom si mentionné]</td><td>[Date si mentionnée]</td></tr>
</table>

<h3>Points en suspens / Questions ouvertes</h3>
<ul>
  <li>[Questions non résolues, sujets à approfondir]</li>
</ul>

<h3>Prochaines étapes</h3>
<p>[Planning détaillé des prochaines actions/réunions]</p>

Note: Adapte les sections selon le contenu. Omets uniquement les sections vraiment non pertinentes.
Sois EXHAUSTIF: inclus tous les détails même s'ils semblent mineurs.
Langue: Français.
PROMPT;

    public function __construct()
    {
        $company = Company::first();
        $this->apiKey = $company?->openai_api_key;
        $this->model = $company?->openai_chat_model ?? 'gpt-4o';
        $this->detailLevel = $company?->summary_detail_level ?? 'concise';
    }

    /**
     * Get the detail level being used.
     */
    public function getDetailLevel(): string
    {
        return $this->detailLevel;
    }

    /**
     * Check if the service is properly configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Get the current model being used.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Generate both short and long summaries from notes and transcription.
     *
     * @param  string  $notes  HTML notes content
     * @param  string  $transcription  Plain text transcription
     * @return array{success: bool, short_summary?: string, long_summary?: string, error?: string}
     */
    public function generateSummaries(string $notes, string $transcription): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'OpenAI API key is not configured. Please set it in Settings.',
            ];
        }

        // Combine and clean content
        $content = $this->prepareContent($notes, $transcription);

        if (empty(trim($content))) {
            return [
                'success' => false,
                'error' => 'No content to summarize.',
            ];
        }

        try {
            // Generate short summary
            $shortSummary = $this->callOpenAI(self::SHORT_SUMMARY_PROMPT, $content);

            if ($shortSummary === null) {
                return [
                    'success' => false,
                    'error' => 'Failed to generate short summary.',
                ];
            }

            // Generate long summary (use appropriate prompt based on detail level)
            $longPrompt = $this->detailLevel === 'exhaustive'
                ? self::LONG_SUMMARY_EXHAUSTIVE_PROMPT
                : self::LONG_SUMMARY_CONCISE_PROMPT;
            $longSummary = $this->callOpenAI($longPrompt, $content);

            if ($longSummary === null) {
                return [
                    'success' => false,
                    'error' => 'Failed to generate long summary.',
                ];
            }

            return [
                'success' => true,
                'short_summary' => $shortSummary,
                'long_summary' => $longSummary,
            ];
        } catch (\Exception $e) {
            Log::error('AiSummaryService error', [
                'message' => $e->getMessage(),
                'model' => $this->model,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate summaries: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare content by combining notes and transcription.
     */
    protected function prepareContent(string $notes, string $transcription): string
    {
        $parts = [];

        // Clean HTML from notes
        if (! empty($notes)) {
            $cleanNotes = strip_tags($notes);
            $cleanNotes = html_entity_decode($cleanNotes, ENT_QUOTES, 'UTF-8');
            $cleanNotes = trim($cleanNotes);

            if (! empty($cleanNotes)) {
                $parts[] = "=== Notes ===\n" . $cleanNotes;
            }
        }

        // Add transcription
        if (! empty($transcription)) {
            $cleanTranscription = trim($transcription);

            if (! empty($cleanTranscription)) {
                $parts[] = "=== Transcription ===\n" . $cleanTranscription;
            }
        }

        return implode("\n\n", $parts);
    }

    /**
     * Check if the model is an o1-series model.
     */
    protected function isO1Model(): bool
    {
        return str_starts_with($this->model, 'o1') || str_starts_with($this->model, 'o3');
    }

    /**
     * Get max tokens based on detail level.
     */
    protected function getMaxTokens(): int
    {
        return $this->detailLevel === 'exhaustive' ? 8000 : 4000;
    }

    /**
     * Call OpenAI Chat Completions API.
     */
    protected function callOpenAI(string $systemPrompt, string $content): ?string
    {
        $maxTokens = $this->getMaxTokens();

        // o1 models have different API requirements
        if ($this->isO1Model()) {
            $payload = [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $systemPrompt . "\n\n---\n\n" . $content,
                    ],
                ],
                'max_completion_tokens' => $maxTokens,
            ];
        } else {
            $payload = [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $content,
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens' => $maxTokens,
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post($this->baseUrl, $payload);

        if (! $response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            Log::error('OpenAI API error', [
                'error' => $error,
                'status' => $response->status(),
                'model' => $this->model,
            ]);

            throw new \Exception("OpenAI API error: {$error}");
        }

        $data = $response->json();

        return $data['choices'][0]['message']['content'] ?? null;
    }
}
