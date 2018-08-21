<?php
/**
 * Nodes routing
 * User: moyo
 * Date: 27/10/2017
 * Time: 4:04 PM
 */

namespace Carno\Cluster\Managed;

use Carno\Cluster\Contracts\Tags;
use Carno\Cluster\Routing;
use function Carno\Coroutine\all;
use Carno\Net\Address;
use Carno\Net\Endpoint;
use Carno\Promise\Promise;
use Carno\Promise\Promised;

trait Router
{
    /**
     * @var array
     */
    protected $tags = Tags::DEFAULT;

    /**
     * @var bool
     */
    protected $strict = true;

    /**
     * @var Routing
     */
    private $routing = null;

    /**
     * @var mixed[]
     */
    private $connected = [];

    /**
     * @return Routing
     */
    public function routing() : Routing
    {
        return
            $this->routing ??
            $this->routing = (new Routing(
                sprintf('%s:%s', $this->type, $this->server),
                function (int $evk, Endpoint $endpoint) {
                    $this->changed($evk, $endpoint);
                }
            ))->strictly($this->strict)->accepts(...$this->tags)
        ;
    }

    /**
     * @param int $evk
     * @param Endpoint $endpoint
     */
    protected function changed(int $evk, Endpoint $endpoint) : void
    {
        switch ($evk) {
            case Routing::JOINING:
                $this->joined(true);
                $this->discovered($endpoint);
                break;
            case Routing::LEAVING:
                $this->disconnected($endpoint);
                break;
        }
    }

    /**
     * @param Endpoint $node
     */
    protected function discovered(Endpoint $node) : void
    {
        // do nothing
    }

    /**
     * @return bool
     */
    protected function clustered() : bool
    {
        return $this->routing()->clustered();
    }

    /**
     * @param Endpoint $node
     * @return mixed
     */
    protected function picked(Endpoint $node)
    {
        return $this->connected($node);
    }

    /**
     * @param string ...$tags
     * @return mixed
     */
    protected function picking(string ...$tags)
    {
        return $this->connected($this->routing()->picking(...$tags));
    }

    /**
     * @return Promised
     */
    protected function releasing() : Promised
    {
        $pending = [];

        foreach ($this->connected as $nodeID => $sar) {
            $pending[] = $this->disconnected((new Endpoint(new Address(0)))->assignID($nodeID));
        }

        return all(...$pending);
    }

    /**
     * @param Endpoint $node
     * @return mixed
     */
    private function connected(Endpoint $node)
    {
        return $this->connected[$node->id()] ?? $this->connected[$node->id()] = $this->connecting($node);
    }

    /**
     * @param Endpoint $node
     * @return Promised
     */
    private function disconnected(Endpoint $node) : Promised
    {
        if ($cn = $this->connected[$node->id()] ?? null) {
            unset($this->connected[$node->id()]);
            return $this->disconnecting($cn);
        } else {
            return Promise::rejected();
        }
    }
}
