<?php
/**
 *  vendor/frank-rachel/jaxon-laravel/src/Jaxon.php
 *
 *  Lightweight back‑compat facade: it just *is* the core Jaxon\App\App.
 *  Do **not** extend the Laravel‑specific class in App\Jaxon to avoid
 *  circular inheritance.
 */
namespace Jaxon\Laravel;

use Jaxon\App\App;            // concrete class shipped with jaxon-core
use Jaxon\App\AppInterface;   // interface jaxon expects

class Jaxon extends App implements AppInterface
{
    // Nothing more: we inherit every needed method from App
}

