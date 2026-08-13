<?php

namespace Modules\Courrier\Contracts;

use Modules\Courrier\Models\Courrier;

/**
 * Point d'extension : génère le PDF définitif d'un courrier au moment
 * précis de sa signature — jamais avant, jamais régénéré ensuite.
 */
interface CourrierPdfGenerator
{
    /**
     * @return string Chemin de stockage (disk "local") du PDF généré.
     */
    public function generer(Courrier $courrier): string;
}
