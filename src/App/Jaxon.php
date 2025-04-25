<?php

namespace Jaxon\Laravel\App;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Jaxon\App\Ajax;
use Jaxon\App\Config\Config;
use Jaxon\App\Response\Manager\ResponseManager;
use Jaxon\App\Request\Factory\RequestFactory;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;

use function asset;
use function config;
use function public_path;
use function response;
use function route;

class Jaxon extends Ajax
{
    /**
     * Build a Jaxon instance with custom Container.
     */
    public function __construct(Config $xConfig, ResponseManager $xResponseManager, RequestFactory $xRequestFactory)
    {
        // Call parent constructor
        parent::__construct($xConfig, $xResponseManager, $xRequestFactory);

        // Set the container (the DI constructor requires an Ajax instance)
        $this->xContainer = new Container(
            $this,
            $xConfig,
            $xResponseManager,
            $xRequestFactory
        );
    }

    /**
     * Configure Jaxon for the Laravel runtime.
     *
     * @throws SetupException
     */
    public function setup(string $_ = ''): void
    {
        /* --------------------------------------------------------------- *
         |  1)  Blade directives for Jaxon helpers
         * --------------------------------------------------------------- */
        Blade::directive('jxnHtml',       fn($e) => '<?php echo Jaxon\attr()->html('       .$e.'); ?>');
        Blade::directive('jxnBind',       fn($e) => '<?php echo Jaxon\attr()->bind('       .$e.'); ?>');
        Blade::directive('jxnPagination', fn($e) => '<?php echo Jaxon\attr()->pagination(' .$e.'); ?>');
        Blade::directive('jxnOn',         fn($e) => '<?php echo Jaxon\attr()->on('         .$e.'); ?>');
        Blade::directive('jxnClick',      fn($e) => '<?php echo Jaxon\attr()->click('      .$e.'); ?>');
        Blade::directive('jxnEvent',      fn($e) => '<?php echo Jaxon\attr()->event('      .$e.'); ?>');
        Blade::directive('jxnTarget',     fn($e) => '<?php echo Jaxon\attr()->target('     .$e.'); ?>');

        Blade::directive('jxnCss',    fn()    => '<?php echo Jaxon\jaxon()->css(); ?>');
        Blade::directive('jxnJs',     fn()    => '<?php echo Jaxon\jaxon()->js(); ?>');
        Blade::directive('jxnScript', fn($e)  => '<?php echo Jaxon\jaxon()->script(' .$e.'); ?>');

        /* --------------------------------------------------------------- *
         |  2)  Laravel runtime bridges
         * --------------------------------------------------------------- */
        $this->addViewRenderer('blade', '', static fn () => new View());
        $this->setSessionManager(static fn () => new Session());
        $this->setLogger(Log::getLogger());

        /* --------------------------------------------------------------- *
         |  3)  Request URI and configuration
         * --------------------------------------------------------------- */
        if(!config('jaxon.lib.core.request.uri') && ($route = config('jaxon.app.request.route', 'jaxon')))
        {
            $this->uri(route($route));
        }

        // Load Jaxon config and asset settings
        $this->bootstrap()
             ->lib(config('jaxon.lib', []))
             ->app(config('jaxon.app', []))
             ->asset(
                 $export = !config('app.debug', false),
                 $minify = $export,
                 asset('jaxon/js'),
                 public_path('jaxon/js')
             )
             ->setup();
    }

    /**
     * Return the HTTP response for a Jaxon Ajax call.
     */
    public function httpResponse(string $status = '200')
    {
        return response(
            $this->ajaxResponse()->getOutput(),
            $status,
            ['Content-Type' => $this->getContentType()]
        );
    }
}
