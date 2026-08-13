<?php

namespace Modules\Kernel\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Kernel\Contracts\PdfGenerationService;

/**
 * Implémentation actuelle : barryvdh/laravel-dompdf (paquet Composer
 * `barryvdh/laravel-dompdf`, façade Barryvdh\DomPDF\Facade\Pdf). Seul
 * fichier du projet autorisé à référencer cette façade — un remplacement
 * futur (ex. par un service de rendu externe) se limite à réécrire cette
 * classe et son binding dans KernelServiceProvider, sans toucher aux
 * appelants.
 */
class DompdfPdfGenerationService implements PdfGenerationService
{
    public function genererDepuisVue(string $vue, array $donnees = []): string
    {
        return Pdf::loadView($vue, $donnees)->output();
    }
}
