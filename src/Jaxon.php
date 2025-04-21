<?php
namespace Jaxon\Laravel;

/**
 * Back‑compat shim kept only so that older code which still
 * calls  \Jaxon\Laravel\Jaxon::xxx()  does not break.
 *
 * ▸ It must **NOT** introduce a new class hierarchy, it must
 *   simply re‑export the real class.
 */
class Jaxon extends \Jaxon\Laravel\App\Jaxon {}
