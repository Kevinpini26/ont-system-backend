<?php

namespace Modules\Kernel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * "Dernière visite" d'un utilisateur sur une liste donnée (ex.
 * 'courriers_recus', 'demandes_stage') — primitive des badges de
 * notification (voir NotificationCompteurController) : le compteur affiché
 * est simplement "combien d'éléments créés depuis cette date".
 */
class Consultation extends Model
{
    protected $fillable = [
        'user_id',
        'cle',
        'consulte_at',
    ];

    protected function casts(): array
    {
        return [
            'consulte_at' => 'datetime',
        ];
    }

    public static function marquer(User $user, string $cle): void
    {
        self::query()->updateOrCreate(
            ['user_id' => $user->id, 'cle' => $cle],
            ['consulte_at' => now()],
        );
    }

    public static function derniere(User $user, string $cle): ?Carbon
    {
        return self::query()
            ->where('user_id', $user->id)
            ->where('cle', $cle)
            ->value('consulte_at');
    }
}
