<?php

namespace Modules\Stagiaires\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Kernel\Models\User;

class StagiaireProlongation extends Model
{
    protected $fillable = [
        'stagiaire_id',
        'ancienne_date_fin',
        'nouvelle_date_fin',
        'motif',
        'prolonge_par_id',
    ];

    protected function casts(): array
    {
        return [
            'ancienne_date_fin' => 'date',
            'nouvelle_date_fin' => 'date',
        ];
    }

    public function stagiaire(): BelongsTo
    {
        return $this->belongsTo(Stagiaire::class);
    }

    public function prolongePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prolonge_par_id');
    }
}
