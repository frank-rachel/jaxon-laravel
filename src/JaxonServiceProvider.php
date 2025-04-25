<?php
namespace Jaxon\Laravel;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Jaxon\App\AppInterface;
use Jaxon\Config\Config;
use Jaxon\Exception\SetupException;
use Jaxon\Laravel\App\Jaxon as LaravelJaxon;
use Jaxon\Laravel\Middleware\AjaxMiddleware;
use Jaxon\Laravel\Middleware\ConfigMiddleware;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Response\Manager\ResponseManager;

use function config;
use function config_path;
use function Jaxon\jaxon;
use function response;

class JaxonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        jaxon()->di()->set(AppInterface::class, fn () => $this->app->make(LaravelJaxon::class));

        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('jaxon.php'),
        ], 'config');

        $router = $this->app->make('router');
        $router->aliasMiddleware('jaxon.config', ConfigMiddleware::class);
        $router->aliasMiddleware('jaxon.ajax', AjaxMiddleware::class);

        if (is_string($route = config('jaxon.app.request.route'))) {
            $mw = array_unique(array_merge(
                (array)config('jaxon.app.request.middlewares', []),
                ['jaxon.config', 'jaxon.ajax']
            ));
            $router->post($route, fn () => response()->json([]))
                   ->middleware($mw)
                   ->name('jaxon');
        }

        if (config('jaxon.app.helpers.load', true)) {
            require_once config('jaxon.app.helpers.path', __DIR__ . '/helpers.php');
        }
    }

    public function register(): void
    {
        // Let Laravel build these classes
        $this->app->bind(Config::class, fn() => new Config([]));
        $this->app->bind(ResponseManager::class, fn() => new ResponseManager());
        $this->app->bind(RequestFactory::class, fn() => new RequestFactory());

        $this->app->singleton(LaravelJaxon::class, function(Container $app) {
            $jaxon = new LaravelJaxon();
            $jaxon->setup();
            return $jaxon;
        });
    }
}
