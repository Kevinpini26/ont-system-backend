<?php

namespace Modules\Stagiaires\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Courrier\Events\CourrierStageAvisFavorable;
use Modules\Stagiaires\Listeners\CreerFicheStagiaireDepuisCourrier;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        CourrierStageAvisFavorable::class => [
            CreerFicheStagiaireDepuisCourrier::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = false;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
