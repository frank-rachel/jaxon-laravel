<?php
/**
 *  vendor/frank-rachel/jaxon-laravel/src/JaxonServiceProvider.php
 */

namespace Jaxon\Laravel;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Jaxon\App\AppInterface;
use Jaxon\App\Config\Config;
use Jaxon\App\Request\Factory\RequestFactory;
use Jaxon\App\Response\Manager\ResponseManager;
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
        // Provide the Jaxon\App\AppInterface, used by jaxon()->di() calls.
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

        if (is_string($route = config('jaxon.app.request.route'))) {
            $mw = array_unique(array_merge(
                (array)config('jaxon.app.request.middlewares', []),
                ['jaxon.config', 'jaxon.ajax']
            ));
            $router->post($route, fn() => response()->json([]))
                   ->middleware($mw)
                   ->name('jaxon');
        }

        if (config('jaxon.app.helpers.load', true)) {
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
        /**
         * 1) Bind the classes needed by the Jaxon constructor.
         *    Adjust the array config below or load from a config file if desired.
         */
        $this->app->bind(Config::class, function() {
            // Provide Jaxon with a base config array if needed
            return new Config([]);
        });

        $this->app->bind(ResponseManager::class, function() {
            return new ResponseManager();
        });

        $this->app->bind(RequestFactory::class, function() {
            return new RequestFactory();
        });

        /**
         * 2) Register the main LaravelJaxon singleton.
         */
        $this->app->singleton(LaravelJaxon::class, function (Container $app) {
            $jaxon = new LaravelJaxon(
                $app->make(Config::class),
                $app->make(ResponseManager::class),
                $app->make(RequestFactory::class)
            );
            $jaxon->setup();
            return $jaxon;
        });
    }
}
