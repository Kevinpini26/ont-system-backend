<?php

namespace Modules\Stagiaires\Support;

use Illuminate\Support\Facades\Storage;
use Modules\Kernel\Contracts\PdfGenerationService;
use Modules\Stagiaires\Contracts\ConventionGenerator;
use Modules\Stagiaires\Models\Stagiaire;

class DompdfConventionGenerator implements ConventionGenerator
{
    public function __construct(private readonly PdfGenerationService $pdf) {}

    public function generer(Stagiaire $stagiaire): string
    {
        $contenu = $this->pdf->genererDepuisVue('stagiaires::convention', ['stagiaire' => $stagiaire]);

        $chemin = "conventions/convention-stagiaire-{$stagiaire->id}.pdf";

        Storage::disk('local')->put($chemin, $contenu);

        return $chemin;
    }
}
