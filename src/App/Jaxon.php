<?php
namespace Jaxon\Laravel\App;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Jaxon\App\App;           // The new base class
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use function asset;
use function config;
use function public_path;
use function response;
use function route;

class Jaxon extends App
{
    public function __construct()
    {
        // Pass this instance to Jaxon’s DI container
        parent::__construct(new Container($this));
    }

    public function setup(string $_ = ''): void
    {
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

        $this->setLogger(Log::getLogger());
        $this->addViewRenderer('blade', '', static fn () => new View());
        $this->setSessionManager(static fn () => new Session());

        if(!config('jaxon.lib.core.request.uri') && ($route = config('jaxon.app.request.route', 'jaxon')))
        {
            $this->uri(route($route));
        }

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

    public function httpResponse(string $status = '200')
    {
        return response(
            $this->ajaxResponse()->getOutput(),
            $status,
            ['Content-Type' => $this->getContentType()]
        );
    }
}
