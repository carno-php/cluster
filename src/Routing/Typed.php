<?php
/**
 * Typed API
 * User: moyo
 * Date: 2018/4/20
 * Time: 2:21 PM
 */

namespace Carno\Cluster\Routing;

use Carno\Net\Endpoint;

interface Typed
{
    /**
     * @param string ...$tags
     * @return Endpoint[]
     */
    public function picked(string ...$tags) : array;

    /**
     * @param string $tag
     * @param Endpoint $node
     */
    public function classify(string $tag, Endpoint $node) : void;

    /**
     * @param Endpoint $node
     */
    public function release(Endpoint $node) : void;
}
