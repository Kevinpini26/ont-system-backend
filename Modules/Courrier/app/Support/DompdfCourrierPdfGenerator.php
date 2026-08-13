<?php

namespace Modules\Courrier\Support;

use Illuminate\Support\Facades\Storage;
use Modules\Courrier\Contracts\CourrierPdfGenerator;
use Modules\Courrier\Models\Courrier;
use Modules\Kernel\Contracts\PdfGenerationService;

class DompdfCourrierPdfGenerator implements CourrierPdfGenerator
{
    public function __construct(
        private readonly TipTapHtmlRenderer $rendu,
        private readonly PdfGenerationService $pdf,
    ) {}

    public function generer(Courrier $courrier): string
    {
        $contenu = $this->pdf->genererDepuisVue('courrier::courrier-signe', [
            'courrier' => $courrier->loadMissing('signataire'),
            'corpsHtml' => $this->rendu->render($courrier->projet_reponse_contenu),
        ]);

        $chemin = "courriers-signes/courrier-{$courrier->id}.pdf";

        Storage::disk('local')->put($chemin, $contenu);

        return $chemin;
    }
}
