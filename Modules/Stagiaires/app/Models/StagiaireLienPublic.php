<?php

namespace Modules\Stagiaires\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Stagiaires\Enums\TypeLienPublic;

class StagiaireLienPublic extends Model
{
    // Le pluriel Eloquent par défaut ("stagiaire_lien_publics") pluralise le
    // mauvais mot ; "stagiaire_liens_publics" est grammaticalement correct.
    protected $table = 'stagiaire_liens_publics';

    public $timestamps = false;

    protected $fillable = [
        'stagiaire_id',
        'type',
        'token',
        'consomme_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => TypeLienPublic::class,
            'consomme_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public static function genererPour(Stagiaire $stagiaire, TypeLienPublic $type): self
    {
        return self::query()->create([
            'stagiaire_id' => $stagiaire->id,
            'type' => $type,
            'token' => Str::random(48),
            'created_at' => now(),
        ]);
    }

    public function estValide(): bool
    {
        return $this->consomme_at === null;
    }

    public function consommer(): void
    {
        $this->update(['consomme_at' => now()]);
    }

    public function stagiaire(): BelongsTo
    {
        return $this->belongsTo(Stagiaire::class);
    }
}
