<?php
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
    public function boot(): void
    {
        jaxon()->di()->set(AppInterface::class, function () {
            return $this->app->make(LaravelJaxon::class);
        });

        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('jaxon.php'),
        ], 'config');

        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');

        $router->aliasMiddleware('jaxon.config', ConfigMiddleware::class);
        $router->aliasMiddleware('jaxon.ajax', AjaxMiddleware::class);

        if(($sRoute = config('jaxon.app.request.route', 'jaxon')) && is_string($sRoute))
        {
            $aMiddlewares = array_unique(array_merge(
                (array)config('jaxon.app.request.middlewares', []),
                ['jaxon.config', 'jaxon.ajax']
            ));
            $router->post($sRoute, fn () => response()->json([]))
                ->middleware($aMiddlewares)
                ->name('jaxon');
        }

        if(config('jaxon.app.helpers.load', true))
        {
            require_once config('jaxon.app.helpers.path', __DIR__ . '/helpers.php');
        }
    }

    /**
     * Register the single, shared Jaxon instance.
     *
     * @throws SetupException
     */
    public function register(): void
    {
        $this->app->singleton(LaravelJaxon::class, function (Container $app) {
            $jaxon = new LaravelJaxon();
            $jaxon->setup();
            return $jaxon;
        });
    }
}
