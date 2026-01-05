<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\Invoice;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfGeneratorService
{
    /**
     * Génère un PDF pour un devis
     *
     * @param Quote $quote
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateQuotePdf(Quote $quote): \Barryvdh\DomPDF\PDF
    {
        // Chargement des relations
        $quote->load(['client', 'lines', 'project']);

        // Recalcul des totaux pour s'assurer qu'ils sont à jour
        $quote->calculateTotals();

        // Récupération des infos société
        $company = Company::first();

        // Préparation des données
        $data = [
            'document' => $quote,
            'company' => $company,
            'client' => $quote->client,
            'lines' => $quote->lines,
            'type' => 'quote',
            'title' => __('resources.quote.pdf_title'),
        ];

        // Génération du PDF
        return Pdf::loadView('pdfs.document', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('enable_remote', false)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', false);
    }

    /**
     * Génère un PDF pour une facture
     *
     * @param Invoice $invoice
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateInvoicePdf(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        // Chargement des relations
        $invoice->load(['client', 'lines', 'project']);

        // Recalcul des totaux pour s'assurer qu'ils sont à jour
        $invoice->calculateTotals();

        // Récupération des infos société
        $company = Company::first();

        // Préparation des données
        $data = [
            'document' => $invoice,
            'company' => $company,
            'client' => $invoice->client,
            'lines' => $invoice->lines,
            'type' => 'invoice',
            'title' => __('resources.invoice.pdf_title'),
        ];

        // Génération du PDF
        return Pdf::loadView('pdfs.document', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('enable_remote', false)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', false);
    }

    /**
     * Génère le nom de fichier pour le PDF
     *
     * @param Quote|Invoice $document
     * @param string $type 'quote' or 'invoice'
     * @return string
     */
    public function getFilename($document, string $type): string
    {
        $prefix = $type === 'quote' ? 'Devis' : 'Facture';
        $number = str_replace('/', '-', $document->number);
        $clientName = $this->sanitizeFilename($document->client->display_name);

        return "{$prefix}_{$number}_{$clientName}.pdf";
    }

    /**
     * Nettoie le nom de fichier pour éviter les caractères problématiques
     *
     * @param string $name
     * @return string
     */
    private function sanitizeFilename(string $name): string
    {
        // Suppression des caractères spéciaux et espaces
        $name = preg_replace('/[^A-Za-z0-9\-]/', '_', $name);
        // Limitation de la longueur
        return substr($name, 0, 50);
    }
}
