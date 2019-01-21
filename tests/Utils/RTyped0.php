<?php
/**
 * Routing typed base
 * User: moyo
 * Date: 2019-01-21
 * Time: 11:12
 */

namespace Carno\Cluster\Tests\Utils;

use Carno\Net\Endpoint;

abstract class RTyped0
{
    /**
     * @var string
     */
    protected $tagged = null;

    /**
     * @var Endpoint
     */
    protected $endpoint = null;

    /**
     * @return bool
     */
    public function available() : bool
    {
        return !! $this->endpoint;
    }

    /**
     * @param string $tag
     * @param Endpoint $node
     */
    public function classify(string $tag, Endpoint $node) : void
    {
        if ($tag === $this->tagged) {
            $this->endpoint = $node;
        }
    }

    /**
     * @param string ...$tags
     * @return array
     */
    public function picked(string ...$tags) : array
    {
        return empty(array_diff([$this->tagged], $tags)) && $this->endpoint ? [$this->endpoint] : [];
    }

    /**
     * @param Endpoint $node
     */
    public function release(Endpoint $node) : void
    {
        if ($this->endpoint && $this->endpoint->id() === $node->id()) {
            $this->endpoint = null;
        }
    }
}
