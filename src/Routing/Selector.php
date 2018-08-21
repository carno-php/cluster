<?php
/**
 * Route selector
 * User: moyo
 * Date: 27/10/2017
 * Time: 4:23 PM
 */

namespace Carno\Cluster\Routing;

use Carno\Cluster\Contracts\Tags;
use Carno\Cluster\Exception\UnavailableServiceNodesException;
use Carno\Cluster\Routing\Types\Forced;
use Carno\Net\Endpoint;

class Selector
{
    /**
     * @var string
     */
    private $serviced = null;

    /**
     * @var Endpoint[]
     */
    private $nodes = [];

    /**
     * @var Endpoint[][]
     */
    private $tagged = [];

    /**
     * @var Typeset
     */
    private $typeset = null;

    /**
     * Selector constructor.
     * @param string $serviced
     */
    public function __construct(string $serviced)
    {
        $this->serviced = $serviced;
        $this->typeset = new Typeset(new Forced);
    }

    /**
     * @return Typeset
     */
    public function typeset() : Typeset
    {
        return $this->typeset;
    }

    /**
     * @return bool
     */
    public function available() : bool
    {
        return count($this->nodes) > 0;
    }

    /**
     * @return bool
     */
    public function clustered() : bool
    {
        return count($this->nodes) > 1;
    }

    /**
     * @param string ...$tags
     * @return Endpoint
     */
    public function picking(string ...$tags) : Endpoint
    {
        // type forced
        if ($nodes = $this->typeset->picked()) {
            goto RND_PICK;
        }

        // tagged searching
        foreach ($tags as $tag) {
            if ($nodes = $this->tagged[$tag] ?? []) {
                goto RND_PICK;
            }
        }

        // fallback to master
        if ($nodes = $this->tagged[Tags::MASTER] ?? []) {
            goto RND_PICK;
        }

        // no services
        throw new UnavailableServiceNodesException($this->serviced);

        // random picking
        RND_PICK: return $nodes[array_rand($nodes)];
    }

    /**
     * @param Endpoint $node
     */
    public function classify(Endpoint $node) : void
    {
        foreach ($node->getTags() ?: Tags::DEFAULT as $tag) {
            if (substr($tag, 0, $cl = strlen(Tags::CMD)) === Tags::CMD) {
                $this->typeset->classify(substr($tag, $cl), $node);
            } else {
                $this->tagged[$tag][$node->id()] = $node;
            }
        }

        $this->nodes[$node->id()] = $node;
    }

    /**
     * @param Endpoint $node
     */
    public function release(Endpoint $node) : void
    {
        $this->typeset->release($node);

        foreach ($this->tagged as $tag => $nodes) {
            if (isset($nodes[$node->id()])) {
                unset($this->tagged[$tag][$node->id()]);
            }
        }

        unset($this->nodes[$node->id()]);
    }
}
