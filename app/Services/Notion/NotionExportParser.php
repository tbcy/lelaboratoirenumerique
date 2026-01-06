<?php

namespace App\Services\Notion;

use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Exception\CommonMarkException;

class NotionExportParser
{
    private CommonMarkConverter $markdownConverter;

    public function __construct()
    {
        $this->markdownConverter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * Parse the CSV file containing meeting metadata.
     */
    public function parseCSV(string $csvPath): array
    {
        if (!file_exists($csvPath)) {
            throw new \RuntimeException("CSV file not found: {$csvPath}");
        }

        $results = [];
        $handle = fopen($csvPath, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Cannot open CSV file: {$csvPath}");
        }

        // Read header row
        $headers = fgetcsv($handle);

        if ($headers === false) {
            fclose($handle);
            throw new \RuntimeException("Cannot read CSV headers");
        }

        // Remove BOM if present
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

        // Normalize headers
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);

            if ($data === false) {
                continue;
            }

            $results[] = [
                'name' => $data['name'] ?? '',
                'date' => $this->parseDate($data['date'] ?? ''),
                'meeting_summary' => $data['meeting summary'] ?? '',
                'participants' => $this->parseParticipants($data['participants'] ?? ''),
                'scope' => $data['scope'] ?? '',
            ];
        }

        fclose($handle);

        return $results;
    }

    /**
     * Parse a Markdown file and extract sections.
     */
    public function parseMarkdown(string $mdPath): array
    {
        if (!file_exists($mdPath)) {
            return [
                'title' => '',
                'resume' => '',
                'notes' => '',
                'transcription' => '',
            ];
        }

        $content = file_get_contents($mdPath);

        if ($content === false) {
            return [
                'title' => '',
                'resume' => '',
                'notes' => '',
                'transcription' => '',
            ];
        }

        // Extract title from first line
        $title = '';
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            $title = trim($matches[1]);
        }

        // Extract sections
        $resume = $this->extractSection($content, 'Résumé', 'Notes');
        $notes = $this->extractSection($content, 'Notes', 'Transcription');
        $transcription = $this->extractSection($content, 'Transcription', null);

        return [
            'title' => $title,
            'resume' => $this->markdownToHtml($resume),
            'notes' => $this->markdownToHtml($notes),
            'transcription' => $this->cleanTranscription($transcription),
        ];
    }

    /**
     * Extract content between two section markers.
     */
    public function extractSection(string $content, string $startMarker, ?string $endMarker): string
    {
        // Find the start marker (as a standalone line)
        $pattern = '/^' . preg_quote($startMarker, '/') . '\s*$/m';

        if (!preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            return '';
        }

        $startPos = $matches[0][1] + strlen($matches[0][0]);

        // Find the end marker or use end of content
        if ($endMarker !== null) {
            $endPattern = '/^' . preg_quote($endMarker, '/') . '\s*$/m';

            if (preg_match($endPattern, $content, $endMatches, PREG_OFFSET_CAPTURE, $startPos)) {
                $endPos = $endMatches[0][1];
            } else {
                $endPos = strlen($content);
            }
        } else {
            $endPos = strlen($content);
        }

        $section = substr($content, $startPos, $endPos - $startPos);

        return trim($section);
    }

    /**
     * Convert Markdown to HTML.
     */
    public function markdownToHtml(string $markdown): string
    {
        if (empty(trim($markdown))) {
            return '';
        }

        try {
            $html = $this->markdownConverter->convert($markdown)->getContent();

            // Clean up the HTML
            $html = trim($html);

            return $html;
        } catch (CommonMarkException $e) {
            // Fallback: basic conversion
            return nl2br(htmlspecialchars($markdown));
        }
    }

    /**
     * Clean transcription text.
     */
    private function cleanTranscription(string $transcription): string
    {
        // Remove image references
        $transcription = preg_replace('/!\[.*?\]\(.*?\)/', '', $transcription);

        // Clean up extra whitespace
        $transcription = preg_replace('/\n{3,}/', "\n\n", $transcription);

        return trim($transcription);
    }

    /**
     * Parse date string to DateTime or null.
     */
    private function parseDate(string $dateStr): ?\DateTime
    {
        if (empty($dateStr)) {
            return null;
        }

        // Try common French date formats
        $formats = [
            'd F Y',      // 29 août 2025
            'j F Y',      // 6 janvier 2026
            'd/m/Y',      // 29/08/2025
            'Y-m-d',      // 2025-08-29
        ];

        // French month names to English
        $frenchMonths = [
            'janvier' => 'January',
            'février' => 'February',
            'mars' => 'March',
            'avril' => 'April',
            'mai' => 'May',
            'juin' => 'June',
            'juillet' => 'July',
            'août' => 'August',
            'septembre' => 'September',
            'octobre' => 'October',
            'novembre' => 'November',
            'décembre' => 'December',
        ];

        $normalizedDate = str_ireplace(
            array_keys($frenchMonths),
            array_values($frenchMonths),
            $dateStr
        );

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $normalizedDate);

            if ($date !== false) {
                return $date;
            }
        }

        // Try strtotime as fallback
        $timestamp = strtotime($normalizedDate);

        if ($timestamp !== false) {
            return (new \DateTime())->setTimestamp($timestamp);
        }

        return null;
    }

    /**
     * Parse participants string to array of names.
     */
    private function parseParticipants(string $participants): array
    {
        if (empty($participants)) {
            return [];
        }

        // Split by comma and clean up
        $names = explode(',', $participants);

        return array_filter(array_map('trim', $names));
    }

    /**
     * Find the Markdown file matching a meeting name.
     */
    public function findMarkdownFile(string $directory, string $meetingName): ?string
    {
        if (!is_dir($directory)) {
            return null;
        }

        // Normalize the meeting name for comparison
        $normalizedName = $this->normalizeFileName($meetingName);

        $files = scandir($directory);

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'md') {
                continue;
            }

            $fileBaseName = pathinfo($file, PATHINFO_FILENAME);

            // Remove the Notion ID suffix (e.g., " 25e09d30a144807f9740c21886dc32b5")
            $cleanName = preg_replace('/\s+[a-f0-9]{32}$/i', '', $fileBaseName);
            $normalizedFileName = $this->normalizeFileName($cleanName);

            if ($normalizedFileName === $normalizedName) {
                return $directory . '/' . $file;
            }
        }

        // Try fuzzy matching if exact match not found
        $bestMatch = null;
        $bestScore = 0;

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'md') {
                continue;
            }

            $fileBaseName = pathinfo($file, PATHINFO_FILENAME);
            $cleanName = preg_replace('/\s+[a-f0-9]{32}$/i', '', $fileBaseName);

            similar_text(
                strtolower($meetingName),
                strtolower($cleanName),
                $similarity
            );

            if ($similarity > $bestScore && $similarity > 70) {
                $bestScore = $similarity;
                $bestMatch = $directory . '/' . $file;
            }
        }

        return $bestMatch;
    }

    /**
     * Normalize a file name for comparison.
     */
    private function normalizeFileName(string $name): string
    {
        // Replace special characters that Notion escapes in filenames
        $name = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], ' ', $name);

        // Normalize whitespace
        $name = preg_replace('/\s+/', ' ', $name);

        return strtolower(trim($name));
    }
}
