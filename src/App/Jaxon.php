<?php
namespace Jaxon\Laravel\App;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
// Notice it's AbstractApp, from jaxon-core 4.7
use Jaxon\App\Ajax\AbstractApp; 
use Jaxon\Exception\SetupException;

use function asset;
use function config;
use function public_path;
use function route;
use function response;

class Jaxon extends AbstractApp
{
    /**
     * Configure Jaxon for the Laravel runtime.
     *
     * @param string $sConfig
     *
     * @throws SetupException
     */
    public function setup(string $sConfig = ''): void
    {
        // Blade directives
        Blade::directive('jxnHtml', fn($e) => '<?php echo Jaxon\\attr()->html(' .$e.'); ?>');
        Blade::directive('jxnBind', fn($e) => '<?php echo Jaxon\\attr()->bind(' .$e.'); ?>');
        Blade::directive('jxnPagination', fn($e) => '<?php echo Jaxon\\attr()->pagination(' .$e.'); ?>');
        Blade::directive('jxnOn', fn($e) => '<?php echo Jaxon\\attr()->on(' .$e.'); ?>');
        Blade::directive('jxnClick', fn($e) => '<?php echo Jaxon\\attr()->click(' .$e.'); ?>');
        Blade::directive('jxnEvent', fn($e) => '<?php echo Jaxon\\attr()->event(' .$e.'); ?>');
        Blade::directive('jxnTarget', fn($e) => '<?php echo Jaxon\\attr()->target(' .$e.'); ?>');

        Blade::directive('jxnCss', fn() => '<?php echo Jaxon\\jaxon()->css(); ?>');
        Blade::directive('jxnJs', fn() => '<?php echo Jaxon\\jaxon()->js(); ?>');
        Blade::directive('jxnScript', fn($e) => '<?php echo Jaxon\\jaxon()->script(' .$e.'); ?>');

        // Logger and session
        $this->setLogger(Log::getLogger());
        $this->addViewRenderer('blade', '', static fn () => new View());
        $this->setSessionManager(static fn () => new Session());

        // The default request URI
        if(!config('jaxon.lib.core.request.uri') && ($sRoute = config('jaxon.app.request.route', 'jaxon')))
        {
            $this->uri(route($sRoute));
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

    /**
     * Return the HTTP response for a Jaxon Ajax call.
     *
     * @param string $status
     * @return \Illuminate\Http\Response
     */
    public function httpResponse(string $status = '200')
    {
        return response($this->ajaxResponse()->getOutput(), $status, [
            'Content-Type' => $this->getContentType()
        ]);
    }
}
