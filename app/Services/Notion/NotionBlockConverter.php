<?php

namespace App\Services\Notion;

class NotionBlockConverter
{
    private NotionClient $client;

    public function __construct(NotionClient $client)
    {
        $this->client = $client;
    }

    /**
     * Convert an array of Notion blocks to HTML.
     */
    public function convert(array $blocks): string
    {
        $html = '';
        $listBuffer = [];
        $currentListType = null;

        foreach ($blocks as $block) {
            $type = $block['type'] ?? 'unknown';

            // Handle list items - buffer them to group into ul/ol
            if (in_array($type, ['bulleted_list_item', 'numbered_list_item'])) {
                $listType = $type === 'bulleted_list_item' ? 'ul' : 'ol';

                // If switching list types, flush the buffer
                if ($currentListType !== null && $currentListType !== $listType) {
                    $html .= $this->flushListBuffer($listBuffer, $currentListType);
                    $listBuffer = [];
                }

                $currentListType = $listType;
                $listBuffer[] = $block;
                continue;
            }

            // Flush any pending list items before processing non-list block
            if (!empty($listBuffer)) {
                $html .= $this->flushListBuffer($listBuffer, $currentListType);
                $listBuffer = [];
                $currentListType = null;
            }

            $html .= $this->convertBlock($block);
        }

        // Flush any remaining list items
        if (!empty($listBuffer)) {
            $html .= $this->flushListBuffer($listBuffer, $currentListType);
        }

        return $html;
    }

    /**
     * Convert a single Notion block to HTML.
     */
    public function convertBlock(array $block): string
    {
        $type = $block['type'] ?? 'unknown';
        $content = $block[$type] ?? [];

        return match ($type) {
            'paragraph' => $this->convertParagraph($content, $block),
            'heading_1' => $this->convertHeading($content, 1, $block),
            'heading_2' => $this->convertHeading($content, 2, $block),
            'heading_3' => $this->convertHeading($content, 3, $block),
            'bulleted_list_item' => $this->convertListItem($content, $block),
            'numbered_list_item' => $this->convertListItem($content, $block),
            'to_do' => $this->convertTodo($content, $block),
            'toggle' => $this->convertToggle($content, $block),
            'code' => $this->convertCode($content),
            'quote' => $this->convertQuote($content, $block),
            'callout' => $this->convertCallout($content, $block),
            'divider' => '<hr>',
            'image' => $this->convertImage($content),
            'video' => $this->convertVideo($content),
            'file' => $this->convertFile($content),
            'pdf' => $this->convertPdf($content),
            'bookmark' => $this->convertBookmark($content),
            'embed' => $this->convertEmbed($content),
            'equation' => $this->convertEquation($content),
            'table_of_contents' => '', // Skip TOC
            'breadcrumb' => '', // Skip breadcrumb
            'column_list' => $this->convertColumnList($block),
            'column' => '', // Handled by column_list
            'child_page' => '', // Handled separately in the import command
            'child_database' => '', // Skip databases
            'synced_block' => $this->convertSyncedBlock($block),
            'table' => $this->convertTable($block),
            'table_row' => '', // Handled by table
            default => '', // Skip unknown blocks
        };
    }

    /**
     * Convert rich text array to HTML with annotations.
     */
    public function convertRichText(array $richText): string
    {
        $html = '';

        foreach ($richText as $item) {
            $text = htmlspecialchars($item['plain_text'] ?? $item['text']['content'] ?? '', ENT_QUOTES, 'UTF-8');
            $annotations = $item['annotations'] ?? [];
            $href = $item['href'] ?? null;

            // Apply annotations
            if ($annotations['bold'] ?? false) {
                $text = "<strong>{$text}</strong>";
            }
            if ($annotations['italic'] ?? false) {
                $text = "<em>{$text}</em>";
            }
            if ($annotations['strikethrough'] ?? false) {
                $text = "<del>{$text}</del>";
            }
            if ($annotations['underline'] ?? false) {
                $text = "<u>{$text}</u>";
            }
            if ($annotations['code'] ?? false) {
                $text = "<code>{$text}</code>";
            }

            // Apply color if not default
            $color = $annotations['color'] ?? 'default';
            if ($color !== 'default') {
                $colorClass = $this->getColorClass($color);
                $text = "<span class=\"{$colorClass}\">{$text}</span>";
            }

            // Apply link
            if ($href) {
                $text = "<a href=\"" . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . "\" target=\"_blank\" rel=\"noopener\">{$text}</a>";
            }

            $html .= $text;
        }

        return $html;
    }

    private function convertParagraph(array $content, array $block): string
    {
        $text = $this->convertRichText($content['rich_text'] ?? []);

        if (empty(trim(strip_tags($text)))) {
            return '<p>&nbsp;</p>';
        }

        $html = "<p>{$text}</p>";

        // Handle nested children
        if ($block['has_children'] ?? false) {
            $html .= $this->convertChildren($block['id']);
        }

        return $html;
    }

    private function convertHeading(array $content, int $level, array $block): string
    {
        $text = $this->convertRichText($content['rich_text'] ?? []);
        $html = "<h{$level}>{$text}</h{$level}>";

        // Toggleable headings can have children
        if (($content['is_toggleable'] ?? false) && ($block['has_children'] ?? false)) {
            $children = $this->convertChildren($block['id']);
            $html = "<details><summary><h{$level}>{$text}</h{$level}></summary>{$children}</details>";
        }

        return $html;
    }

    private function convertListItem(array $content, array $block): string
    {
        $text = $this->convertRichText($content['rich_text'] ?? []);
        $html = "<li>{$text}";

        // Handle nested children
        if ($block['has_children'] ?? false) {
            $html .= $this->convertChildren($block['id']);
        }

        $html .= '</li>';

        return $html;
    }

    private function convertTodo(array $content, array $block): string
    {
        $text = $this->convertRichText($content['rich_text'] ?? []);
        $checked = ($content['checked'] ?? false) ? 'checked' : '';
        $html = "<div class=\"todo-item\"><input type=\"checkbox\" {$checked} disabled> {$text}";

        if ($block['has_children'] ?? false) {
            $html .= $this->convertChildren($block['id']);
        }

        $html .= '</div>';

        return $html;
    }

    private function convertToggle(array $content, array $block): string
    {
        $summary = $this->convertRichText($content['rich_text'] ?? []);
        $children = '';

        if ($block['has_children'] ?? false) {
            $children = $this->convertChildren($block['id']);
        }

        return "<details><summary>{$summary}</summary>{$children}</details>";
    }

    private function convertCode(array $content): string
    {
        $code = '';
        foreach ($content['rich_text'] ?? [] as $item) {
            $code .= $item['plain_text'] ?? '';
        }

        $code = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        $language = $content['language'] ?? 'plaintext';

        return "<pre><code class=\"language-{$language}\">{$code}</code></pre>";
    }

    private function convertQuote(array $content, array $block): string
    {
        $text = $this->convertRichText($content['rich_text'] ?? []);
        $html = "<blockquote>{$text}";

        if ($block['has_children'] ?? false) {
            $html .= $this->convertChildren($block['id']);
        }

        $html .= '</blockquote>';

        return $html;
    }

    private function convertCallout(array $content, array $block): string
    {
        $text = $this->convertRichText($content['rich_text'] ?? []);
        $icon = '';

        if (isset($content['icon'])) {
            if ($content['icon']['type'] === 'emoji') {
                $icon = $content['icon']['emoji'] . ' ';
            }
        }

        $color = $content['color'] ?? 'default';
        $colorClass = $this->getColorClass($color);

        $html = "<div class=\"callout {$colorClass}\"><p>{$icon}{$text}</p>";

        if ($block['has_children'] ?? false) {
            $html .= $this->convertChildren($block['id']);
        }

        $html .= '</div>';

        return $html;
    }

    private function convertImage(array $content): string
    {
        $url = $this->getFileUrl($content);

        if (!$url) {
            return '';
        }

        $caption = '';
        if (!empty($content['caption'])) {
            $caption = $this->convertRichText($content['caption']);
        }

        $html = "<figure><img src=\"{$url}\" alt=\"\">";

        if ($caption) {
            $html .= "<figcaption>{$caption}</figcaption>";
        }

        $html .= '</figure>';

        return $html;
    }

    private function convertVideo(array $content): string
    {
        $url = $this->getFileUrl($content);

        if (!$url) {
            return '';
        }

        // Check if it's a YouTube or Vimeo URL
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            return $this->convertYoutubeEmbed($url);
        }

        if (str_contains($url, 'vimeo.com')) {
            return $this->convertVimeoEmbed($url);
        }

        return "<video src=\"{$url}\" controls></video>";
    }

    private function convertYoutubeEmbed(string $url): string
    {
        // Extract video ID
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $url, $matches);
        $videoId = $matches[1] ?? '';

        if (!$videoId) {
            return "<a href=\"{$url}\" target=\"_blank\">{$url}</a>";
        }

        return "<div class=\"video-embed\"><iframe src=\"https://www.youtube.com/embed/{$videoId}\" frameborder=\"0\" allowfullscreen></iframe></div>";
    }

    private function convertVimeoEmbed(string $url): string
    {
        preg_match('/vimeo\.com\/(\d+)/', $url, $matches);
        $videoId = $matches[1] ?? '';

        if (!$videoId) {
            return "<a href=\"{$url}\" target=\"_blank\">{$url}</a>";
        }

        return "<div class=\"video-embed\"><iframe src=\"https://player.vimeo.com/video/{$videoId}\" frameborder=\"0\" allowfullscreen></iframe></div>";
    }

    private function convertFile(array $content): string
    {
        $url = $this->getFileUrl($content);
        $name = $content['name'] ?? 'Download file';

        if (!$url) {
            return '';
        }

        return "<p><a href=\"{$url}\" target=\"_blank\" rel=\"noopener\">{$name}</a></p>";
    }

    private function convertPdf(array $content): string
    {
        $url = $this->getFileUrl($content);

        if (!$url) {
            return '';
        }

        return "<p><a href=\"{$url}\" target=\"_blank\" rel=\"noopener\">View PDF</a></p>";
    }

    private function convertBookmark(array $content): string
    {
        $url = $content['url'] ?? '';

        if (!$url) {
            return '';
        }

        $caption = '';
        if (!empty($content['caption'])) {
            $caption = $this->convertRichText($content['caption']);
        }

        $displayUrl = $caption ?: $url;

        return "<p class=\"bookmark\"><a href=\"{$url}\" target=\"_blank\" rel=\"noopener\">{$displayUrl}</a></p>";
    }

    private function convertEmbed(array $content): string
    {
        $url = $content['url'] ?? '';

        if (!$url) {
            return '';
        }

        return "<div class=\"embed\"><iframe src=\"{$url}\" frameborder=\"0\"></iframe></div>";
    }

    private function convertEquation(array $content): string
    {
        $expression = htmlspecialchars($content['expression'] ?? '', ENT_QUOTES, 'UTF-8');

        return "<div class=\"equation\">{$expression}</div>";
    }

    private function convertColumnList(array $block): string
    {
        if (!($block['has_children'] ?? false)) {
            return '';
        }

        $children = $this->client->getAllBlockChildren($block['id']);
        $columns = [];

        foreach ($children as $child) {
            if (($child['type'] ?? '') === 'column' && ($child['has_children'] ?? false)) {
                $columnBlocks = $this->client->getAllBlockChildren($child['id']);
                $columns[] = $this->convert($columnBlocks);
            }
        }

        if (empty($columns)) {
            return '';
        }

        $columnCount = count($columns);
        $html = "<div class=\"columns columns-{$columnCount}\">";

        foreach ($columns as $columnHtml) {
            $html .= "<div class=\"column\">{$columnHtml}</div>";
        }

        $html .= '</div>';

        return $html;
    }

    private function convertSyncedBlock(array $block): string
    {
        if (!($block['has_children'] ?? false)) {
            return '';
        }

        return $this->convertChildren($block['id']);
    }

    private function convertTable(array $block): string
    {
        if (!($block['has_children'] ?? false)) {
            return '';
        }

        $rows = $this->client->getAllBlockChildren($block['id']);
        $tableContent = $block['table'] ?? [];
        $hasColumnHeader = $tableContent['has_column_header'] ?? false;
        $hasRowHeader = $tableContent['has_row_header'] ?? false;

        $html = '<table>';
        $isFirstRow = true;

        foreach ($rows as $row) {
            if (($row['type'] ?? '') !== 'table_row') {
                continue;
            }

            $cells = $row['table_row']['cells'] ?? [];
            $tag = ($hasColumnHeader && $isFirstRow) ? 'th' : 'td';

            $html .= '<tr>';

            foreach ($cells as $index => $cell) {
                $cellTag = ($hasRowHeader && $index === 0) ? 'th' : $tag;
                $cellContent = $this->convertRichText($cell);
                $html .= "<{$cellTag}>{$cellContent}</{$cellTag}>";
            }

            $html .= '</tr>';
            $isFirstRow = false;
        }

        $html .= '</table>';

        return $html;
    }

    private function convertChildren(string $blockId): string
    {
        $children = $this->client->getAllBlockChildren($blockId);

        return $this->convert($children);
    }

    private function flushListBuffer(array $buffer, string $listType): string
    {
        if (empty($buffer)) {
            return '';
        }

        $html = "<{$listType}>";

        foreach ($buffer as $block) {
            $type = $block['type'];
            $content = $block[$type] ?? [];
            $html .= $this->convertListItem($content, $block);
        }

        $html .= "</{$listType}>";

        return $html;
    }

    private function getFileUrl(array $content): string
    {
        if (isset($content['external']['url'])) {
            return $content['external']['url'];
        }

        if (isset($content['file']['url'])) {
            return $content['file']['url'];
        }

        return '';
    }

    private function getColorClass(string $color): string
    {
        return match ($color) {
            'gray' => 'text-gray',
            'brown' => 'text-brown',
            'orange' => 'text-orange',
            'yellow' => 'text-yellow',
            'green' => 'text-green',
            'blue' => 'text-blue',
            'purple' => 'text-purple',
            'pink' => 'text-pink',
            'red' => 'text-red',
            'gray_background' => 'bg-gray',
            'brown_background' => 'bg-brown',
            'orange_background' => 'bg-orange',
            'yellow_background' => 'bg-yellow',
            'green_background' => 'bg-green',
            'blue_background' => 'bg-blue',
            'purple_background' => 'bg-purple',
            'pink_background' => 'bg-pink',
            'red_background' => 'bg-red',
            default => '',
        };
    }
}
