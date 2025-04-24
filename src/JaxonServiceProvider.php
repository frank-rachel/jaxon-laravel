<?php
/**
 *  vendor/frank-rachel/jaxon-laravel/src/JaxonServiceProvider.php
 *
 *  Patched for jaxon‑core ≥4.8
 */

namespace Jaxon\Laravel;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Jaxon\App\AppInterface;
use Jaxon\Exception\SetupException;
use Jaxon\Laravel\App\Jaxon as LaravelJaxon;
use Jaxon\Laravel\Middleware\AjaxMiddleware;
use Jaxon\Laravel\Middleware\ConfigMiddleware;

use function config;
use function config_path;
use function Jaxon\jaxon;
use function response;

class JaxonServiceProvider extends ServiceProvider
{
    /* -------------------------------------------------------------------- */
    /*  Boot                                                                */
    /* -------------------------------------------------------------------- */
    public function boot(): void
    {
        /** 1.Bind the interface Jaxon expects to the Laravel implementation */
        jaxon()->di()->set(AppInterface::class, function () {
            return $this->app->make(LaravelJaxon::class);
        });

        /** 2.Publish the package config */
        $this->publishes(
            [__DIR__.'/../config/config.php' => config_path('jaxon.php')],
            'config'
        );

        /** 3.Middleware + route that receives Ajax payloads */
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');

        $router->aliasMiddleware('jaxon.config', ConfigMiddleware::class);
        $router->aliasMiddleware('jaxon.ajax',   AjaxMiddleware::class);

        if (is_string($route = config('jaxon.app.request.route'))) {
            $mw = array_unique(array_merge(
                (array)config('jaxon.app.request.middlewares', []),
                ['jaxon.config', 'jaxon.ajax']
            ));

            $router->post($route, fn () => response()->json([]))
                   ->middleware($mw)
                   ->name('jaxon');
        }

        /** 4.Optionally load the helper functions */
        if (config('jaxon.app.helpers.load', true)) {
            require_once config('jaxon.app.helpers.path', __DIR__.'/helpers.php');
        }
    }

    /* -------------------------------------------------------------------- */
    /*  Register                                                            */
    /* -------------------------------------------------------------------- */
    /**
     * Register the single, shared Jaxon instance.
     *
     * @throws SetupException
     */
    public function register(): void
    {
        $this->app->singleton(LaravelJaxon::class, function (Container $app) {
            //  NOTE: jaxon‑core ≥4.8 requires the container in the constructor
            $jaxon = new LaravelJaxon($app);   // ←‑ pass $app here
            // $jaxon->setup('');
            $jaxon->setup();
            return $jaxon;
        });
    }
}
