<?php

namespace Modules\Courrier\Contracts;

interface NumeroGenerator
{
    public function genererAccuseReception(): string;

    public function genererNumeroEnregistrement(): string;
}
