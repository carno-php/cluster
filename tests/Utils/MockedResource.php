<?php
/**
 * Mocked resource
 * User: moyo
 * Date: 2018/11/21
 * Time: 7:43 PM
 */

namespace Carno\Cluster\Tests\Utils;

use Carno\Cluster\Managed;
use Carno\Net\Endpoint;
use Carno\Promise\Promise;
use Carno\Promise\Promised;

class MockedResource extends Managed
{
    /**
     * @var string
     */
    protected $type = 'mock';

    /**
     * @var int
     */
    protected $port = 42;

    /**
     * @var bool
     */
    protected $strict = false;

    /**
     * @var Endpoint[]
     */
    private $endpoints = [];

    /**
     * @return Endpoint[]
     */
    public function endpoints() : array
    {
        return $this->endpoints;
    }

    /**
     * @param Endpoint $node
     */
    public function discovered(Endpoint $node) : void
    {
        $this->endpoints[$this->picked($node)] = $node;
    }

    /**
     * @param Endpoint $endpoint
     * @return string
     */
    public function connecting(Endpoint $endpoint) : string
    {
        return $endpoint->id();
    }

    /**
     * @param string $id
     * @return Promised
     */
    public function disconnecting($id) : Promised
    {
        unset($this->endpoints[$id]);
        return Promise::resolved();
    }
}
