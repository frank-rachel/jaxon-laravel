<?php
/**
 *  vendor/frank-rachel/jaxon-laravel/src/App/Jaxon.php
 *
 *  Tiny shim that lets legacy code refer to the class
 *  Jaxon\Laravel\Jaxon while, under the hood, we simply use the
 *  framework‑agnostic core Jaxon application.
 */

namespace Jaxon\Laravel;

use Jaxon\App\App;            // concrete core class
use Jaxon\App\AppInterface;   // the interface Jaxon expects

class Jaxon extends App implements AppInterface
{
    // No extra code required – every method we need already exists on
    // \Jaxon\App\App.  We just provide the old class‑name for BC.
}
