<?php

namespace App\Providers;

use App\Console\Commands\PermissionSyncCommand;
use App\Scramble\AuthenticationOperationExtension;
use App\Scramble\ModelNotFoundOperationExtension;
use Dedoc\Scramble\Configuration\OperationTransformers;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\OperationExtensions\DeprecationExtension;
use Dedoc\Scramble\Support\OperationExtensions\ErrorResponsesExtension;
use Dedoc\Scramble\Support\OperationExtensions\RequestBodyExtension;
use Dedoc\Scramble\Support\OperationExtensions\RequestEssentialsExtension;
use Dedoc\Scramble\Support\OperationExtensions\ResponseExtension;
use Dedoc\Scramble\Support\OperationExtensions\ResponseHeadersExtension;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
        $this->commands([
            PermissionSyncCommand::class,
        ]);

        $this->configureScramble();
    }

    /**
     * Extensões que anexam exceções à rota precisam rodar antes da
     * ResponseExtension, que é quem transforma as exceções em respostas.
     * As registradas em config/scramble.php rodam depois dela.
     */
    private function configureScramble(): void
    {
        Scramble::configure()->withOperationTransformers(function (OperationTransformers $transformers) {
            $transformers->use([
                RequestEssentialsExtension::class,
                RequestBodyExtension::class,
                ErrorResponsesExtension::class,
                AuthenticationOperationExtension::class,
                ModelNotFoundOperationExtension::class,
                ResponseExtension::class,
                ResponseHeadersExtension::class,
                DeprecationExtension::class,
            ]);
        });
    }
}
