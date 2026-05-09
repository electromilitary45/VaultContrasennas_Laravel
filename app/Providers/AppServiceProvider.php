<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\VaultItemCreated;
use App\Events\VaultItemDeleted;
use App\Events\VaultItemShared;
use App\Events\VaultItemUnshared;
use App\Events\VaultItemUpdated;
use App\Events\VaultItemViewed;
use App\Listeners\LogVaultItemCreated;
use App\Listeners\LogVaultItemDeleted;
use App\Listeners\LogVaultItemShared;
use App\Listeners\LogVaultItemUnshared;
use App\Listeners\LogVaultItemUpdated;
use App\Listeners\LogVaultItemViewed;
use App\Models\VaultItem;
use App\Policies\VaultItemPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        VaultItem::class => VaultItemPolicy::class,
    ];

    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        VaultItemCreated::class => [
            LogVaultItemCreated::class,
        ],
        VaultItemUpdated::class => [
            LogVaultItemUpdated::class,
        ],
        VaultItemDeleted::class => [
            LogVaultItemDeleted::class,
        ],
        VaultItemViewed::class => [
            LogVaultItemViewed::class,
        ],
        VaultItemShared::class => [
            LogVaultItemShared::class,
        ],
        VaultItemUnshared::class => [
            LogVaultItemUnshared::class,
        ],
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar paginación para usar Bootstrap 5 por defecto
        Paginator::defaultView('pagination::bootstrap-5');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-5');
    }
}
