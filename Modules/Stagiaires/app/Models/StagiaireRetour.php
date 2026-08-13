<?php

namespace Modules\Stagiaires\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Retour d'expérience confidentiel : voir la note dans la migration
 * `create_stagiaire_retours_table` — n'est jamais rattaché à
 * StagiaireResource ni exposé à la direction d'accueil.
 */
class StagiaireRetour extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stagiaire_id',
        'note_encadrement',
        'note_missions',
        'note_ambiance',
        'commentaire',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'note_encadrement' => 'integer',
            'note_missions' => 'integer',
            'note_ambiance' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function stagiaire(): BelongsTo
    {
        return $this->belongsTo(Stagiaire::class);
    }
}
