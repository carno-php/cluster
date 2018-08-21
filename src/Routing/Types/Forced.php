<?php
/**
 * Forced routers (local debugging)
 * User: moyo
 * Date: 2018/4/20
 * Time: 11:51 AM
 */

namespace Carno\Cluster\Routing\Types;

use Carno\Cluster\Routing\Typed;
use Carno\Net\Endpoint;

class Forced implements Typed
{
    /**
     * local dev flag
     */
    private const TAG = 'LOCAL_DEV';

    /**
     * @var Endpoint[]
     */
    private $nodes = [];

    /**
     * @param string ...$tags
     * @return Endpoint[]
     */
    public function picked(string ...$tags) : array
    {
        return $this->nodes;
    }

    /**
     * @param string $tag
     * @param Endpoint $node
     */
    public function classify(string $tag, Endpoint $node) : void
    {
        if ($tag === self::TAG) {
            $this->nodes[$node->id()] = $node;
        }
    }

    /**
     * @param Endpoint $node
     */
    public function release(Endpoint $node) : void
    {
        unset($this->nodes[$node->id()]);
    }
}
