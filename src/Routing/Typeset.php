<?php
/**
 * Types manager
 * User: moyo
 * Date: 2018/7/31
 * Time: 6:15 PM
 */

namespace Carno\Cluster\Routing;

use Carno\Net\Endpoint;

class Typeset implements Typed
{
    /**
     * @var Typed[]
     */
    private $table = [];

    /**
     * Typeset constructor.
     * @param Typed ...$defaults
     */
    public function __construct(Typed ...$defaults)
    {
        foreach ($defaults as $typed) {
            $this->extend($typed);
        }
    }

    /**
     * @param Typed $typing
     * @return static
     */
    public function extend(Typed $typing) : self
    {
        $this->table[get_class($typing)] = $typing;
        return $this;
    }

    /**
     * @param string $typing
     * @return static
     */
    public function forget(string $typing) : self
    {
        unset($this->table[$typing]);
        return $this;
    }

    /**
     * @param string ...$tags
     * @return Endpoint[]
     */
    public function picked(string ...$tags) : array
    {
        foreach ($this->table as $typed) {
            if ($nodes = $typed->picked(...$tags)) {
                return $nodes;
            }
        }
        return [];
    }

    /**
     * @param string $tag
     * @param Endpoint $node
     */
    public function classify(string $tag, Endpoint $node) : void
    {
        foreach ($this->table as $typed) {
            $typed->classify($tag, $node);
        }
    }

    /**
     * @param Endpoint $node
     */
    public function release(Endpoint $node) : void
    {
        foreach ($this->table as $typed) {
            $typed->release($node);
        }
    }
}
