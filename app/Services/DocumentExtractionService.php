<?php

namespace App\Services;

use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Log;

class DocumentExtractionService
{
    /**
     * Supported MIME types and their handlers
     */
    private const SUPPORTED_TYPES = [
        // PDF
        'application/pdf' => 'extractPdf',
        // Word
        'application/msword' => 'extractWord',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'extractWord',
        // Excel
        'application/vnd.ms-excel' => 'extractExcel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'extractExcel',
        // PowerPoint
        'application/vnd.ms-powerpoint' => 'extractPowerPoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'extractPowerPoint',
        // Text
        'text/plain' => 'extractText',
        'text/csv' => 'extractText',
    ];

    /**
     * Error codes
     */
    public const ERROR_UNSUPPORTED_TYPE = 'UNSUPPORTED_TYPE';
    public const ERROR_FILE_NOT_FOUND = 'FILE_NOT_FOUND';
    public const ERROR_PASSWORD_PROTECTED = 'PASSWORD_PROTECTED';
    public const ERROR_PARSE_ERROR = 'PARSE_ERROR';
    public const WARNING_LIKELY_SCANNED = 'LIKELY_SCANNED';

    /**
     * Extract text content from a media file
     *
     * @param Media $media The media model from Spatie Media Library
     * @param bool $useCache Whether to use cached extraction
     * @return array{success: bool, text?: string, error_code?: string, error?: string, metadata?: array, cached?: bool, warning?: string}
     */
    public function extractFromMedia(Media $media, bool $useCache = true): array
    {
        // Check if cached extraction exists
        if ($useCache) {
            $cached = $this->getCachedExtraction($media);
            if ($cached !== null) {
                return [
                    'success' => true,
                    'text' => $cached['text'],
                    'metadata' => $cached['metadata'] ?? [],
                    'cached' => true,
                ];
            }
        }

        // Check if file type is supported
        $mimeType = $media->mime_type;
        if (!isset(self::SUPPORTED_TYPES[$mimeType])) {
            return [
                'success' => false,
                'error_code' => self::ERROR_UNSUPPORTED_TYPE,
                'error' => "Unsupported file type: {$mimeType}. Supported types: PDF, Word (.doc, .docx), Excel (.xls, .xlsx), PowerPoint (.ppt, .pptx), Text (.txt, .csv)",
            ];
        }

        // Check if file exists
        $filePath = $media->getPath();
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'error_code' => self::ERROR_FILE_NOT_FOUND,
                'error' => 'File not found on disk',
            ];
        }

        // Extract content using appropriate handler
        $handler = self::SUPPORTED_TYPES[$mimeType];
        $result = $this->$handler($filePath, $media);

        // Cache successful extraction
        if ($result['success'] && $useCache) {
            $this->cacheExtraction($media, $result['text'], $result['metadata'] ?? []);
        }

        return $result;
    }

    /**
     * Check if a MIME type is supported
     */
    public function isSupported(string $mimeType): bool
    {
        return isset(self::SUPPORTED_TYPES[$mimeType]);
    }

    /**
     * Get list of supported MIME types
     */
    public function getSupportedTypes(): array
    {
        return array_keys(self::SUPPORTED_TYPES);
    }

    /**
     * Extract text from PDF files
     */
    private function extractPdf(string $filePath, Media $media): array
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);

            $text = $pdf->getText();
            $details = $pdf->getDetails();
            $pages = $pdf->getPages();

            // Check for likely scanned document (very little text for the file size)
            $textLength = strlen(trim($text));
            $fileSize = $media->size;
            $pageCount = count($pages);

            // Heuristic: less than 100 chars per page on average suggests scanned
            $avgCharsPerPage = $pageCount > 0 ? $textLength / $pageCount : 0;
            $warning = null;

            if ($textLength < 50 || ($avgCharsPerPage < 100 && $fileSize > 50000)) {
                $warning = 'This PDF appears to be scanned or contains mostly images. Text extraction may be incomplete.';
            }

            $result = [
                'success' => true,
                'text' => $text,
                'metadata' => [
                    'page_count' => $pageCount,
                    'title' => $details['Title'] ?? null,
                    'author' => $details['Author'] ?? null,
                    'creator' => $details['Creator'] ?? null,
                    'creation_date' => $details['CreationDate'] ?? null,
                ],
            ];

            if ($warning) {
                $result['warning'] = $warning;
                $result['warning_code'] = self::WARNING_LIKELY_SCANNED;
            }

            return $result;

        } catch (\Exception $e) {
            Log::warning('PDF extraction failed', [
                'media_id' => $media->id,
                'file' => $media->file_name,
                'error' => $e->getMessage(),
            ]);

            // Check for password protection
            if (str_contains($e->getMessage(), 'password') || str_contains($e->getMessage(), 'encrypted')) {
                return [
                    'success' => false,
                    'error_code' => self::ERROR_PASSWORD_PROTECTED,
                    'error' => 'This PDF is password protected',
                ];
            }

            return [
                'success' => false,
                'error_code' => self::ERROR_PARSE_ERROR,
                'error' => 'Failed to parse PDF: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Extract text from Word documents (.doc, .docx)
     */
    private function extractWord(string $filePath, Media $media): array
    {
        try {
            $phpWord = WordIOFactory::load($filePath);

            $text = '';
            $sectionCount = 0;

            foreach ($phpWord->getSections() as $section) {
                $sectionCount++;
                foreach ($section->getElements() as $element) {
                    $text .= $this->extractWordElement($element);
                }
            }

            return [
                'success' => true,
                'text' => trim($text),
                'metadata' => [
                    'section_count' => $sectionCount,
                ],
            ];

        } catch (\Exception $e) {
            Log::warning('Word extraction failed', [
                'media_id' => $media->id,
                'file' => $media->file_name,
                'error' => $e->getMessage(),
            ]);

            if (str_contains($e->getMessage(), 'password') || str_contains($e->getMessage(), 'encrypted')) {
                return [
                    'success' => false,
                    'error_code' => self::ERROR_PASSWORD_PROTECTED,
                    'error' => 'This Word document is password protected',
                ];
            }

            return [
                'success' => false,
                'error_code' => self::ERROR_PARSE_ERROR,
                'error' => 'Failed to parse Word document: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Recursively extract text from Word elements
     */
    private function extractWordElement($element): string
    {
        $text = '';

        if (method_exists($element, 'getText')) {
            $text .= $element->getText() . "\n";
        }

        if (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $child) {
                $text .= $this->extractWordElement($child);
            }
        }

        return $text;
    }

    /**
     * Extract text from Excel files (.xls, .xlsx)
     */
    private function extractExcel(string $filePath, Media $media): array
    {
        try {
            $spreadsheet = SpreadsheetIOFactory::load($filePath);

            $text = '';
            $sheetCount = $spreadsheet->getSheetCount();
            $rowCount = 0;

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetName = $sheet->getTitle();
                $text .= "=== Sheet: {$sheetName} ===\n\n";

                foreach ($sheet->getRowIterator() as $row) {
                    $rowCount++;
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $value = $cell->getValue();
                        if ($value !== null && $value !== '') {
                            $rowData[] = (string) $value;
                        }
                    }

                    if (!empty($rowData)) {
                        $text .= implode("\t", $rowData) . "\n";
                    }
                }

                $text .= "\n";
            }

            return [
                'success' => true,
                'text' => trim($text),
                'metadata' => [
                    'sheet_count' => $sheetCount,
                    'total_rows' => $rowCount,
                ],
            ];

        } catch (\Exception $e) {
            Log::warning('Excel extraction failed', [
                'media_id' => $media->id,
                'file' => $media->file_name,
                'error' => $e->getMessage(),
            ]);

            if (str_contains($e->getMessage(), 'password') || str_contains($e->getMessage(), 'encrypted')) {
                return [
                    'success' => false,
                    'error_code' => self::ERROR_PASSWORD_PROTECTED,
                    'error' => 'This Excel file is password protected',
                ];
            }

            return [
                'success' => false,
                'error_code' => self::ERROR_PARSE_ERROR,
                'error' => 'Failed to parse Excel file: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Extract text from PowerPoint files (.ppt, .pptx)
     */
    private function extractPowerPoint(string $filePath, Media $media): array
    {
        try {
            $presentation = PresentationIOFactory::load($filePath);

            $text = '';
            $slideCount = $presentation->getSlideCount();
            $slideNumber = 0;

            foreach ($presentation->getAllSlides() as $slide) {
                $slideNumber++;
                $text .= "=== Slide {$slideNumber} ===\n\n";

                foreach ($slide->getShapeCollection() as $shape) {
                    $text .= $this->extractPowerPointShape($shape);
                }

                $text .= "\n";
            }

            return [
                'success' => true,
                'text' => trim($text),
                'metadata' => [
                    'slide_count' => $slideCount,
                ],
            ];

        } catch (\Exception $e) {
            Log::warning('PowerPoint extraction failed', [
                'media_id' => $media->id,
                'file' => $media->file_name,
                'error' => $e->getMessage(),
            ]);

            if (str_contains($e->getMessage(), 'password') || str_contains($e->getMessage(), 'encrypted')) {
                return [
                    'success' => false,
                    'error_code' => self::ERROR_PASSWORD_PROTECTED,
                    'error' => 'This PowerPoint file is password protected',
                ];
            }

            return [
                'success' => false,
                'error_code' => self::ERROR_PARSE_ERROR,
                'error' => 'Failed to parse PowerPoint file: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Recursively extract text from PowerPoint shapes
     */
    private function extractPowerPointShape($shape): string
    {
        $text = '';

        if (method_exists($shape, 'getText')) {
            $shapeText = $shape->getText();
            if (!empty($shapeText)) {
                $text .= $shapeText . "\n";
            }
        }

        // Handle rich text
        if (method_exists($shape, 'getParagraphs')) {
            foreach ($shape->getParagraphs() as $paragraph) {
                foreach ($paragraph->getRichTextElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText();
                    }
                }
                $text .= "\n";
            }
        }

        // Handle grouped shapes
        if (method_exists($shape, 'getShapeCollection')) {
            foreach ($shape->getShapeCollection() as $childShape) {
                $text .= $this->extractPowerPointShape($childShape);
            }
        }

        return $text;
    }

    /**
     * Extract text from plain text files (.txt, .csv)
     */
    private function extractText(string $filePath, Media $media): array
    {
        try {
            $text = file_get_contents($filePath);

            if ($text === false) {
                return [
                    'success' => false,
                    'error_code' => self::ERROR_PARSE_ERROR,
                    'error' => 'Failed to read text file',
                ];
            }

            // Detect and convert encoding if needed
            $encoding = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                $text = mb_convert_encoding($text, 'UTF-8', $encoding);
            }

            $lineCount = substr_count($text, "\n") + 1;

            return [
                'success' => true,
                'text' => $text,
                'metadata' => [
                    'line_count' => $lineCount,
                    'encoding' => $encoding ?: 'unknown',
                ],
            ];

        } catch (\Exception $e) {
            Log::warning('Text extraction failed', [
                'media_id' => $media->id,
                'file' => $media->file_name,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error_code' => self::ERROR_PARSE_ERROR,
                'error' => 'Failed to read text file: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get cached extraction from media custom properties
     */
    private function getCachedExtraction(Media $media): ?array
    {
        $cached = $media->getCustomProperty('extracted_text');

        if ($cached && isset($cached['text'])) {
            return $cached;
        }

        return null;
    }

    /**
     * Cache extraction in media custom properties
     */
    private function cacheExtraction(Media $media, string $text, array $metadata): void
    {
        $media->setCustomProperty('extracted_text', [
            'text' => $text,
            'metadata' => $metadata,
            'extracted_at' => now()->toIso8601String(),
        ]);
        $media->save();
    }

    /**
     * Clear cached extraction for a media
     */
    public function clearCache(Media $media): void
    {
        $media->forgetCustomProperty('extracted_text');
        $media->save();
    }
}
