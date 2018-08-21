<?php
/**
 * Discovering via config
 * User: moyo
 * Date: 2018/8/13
 * Time: 11:59 AM
 */

namespace Carno\Cluster\Discover\Adaptors;

use Carno\Cluster\Discover\Discovered;
use Carno\Cluster\Managed;
use Carno\Config\Config as Source;
use Carno\Net\Address;
use Carno\Net\Endpoint;
use Carno\Promise\Promised;

class Config implements Discovered
{
    /**
     * @var Source
     */
    private $source = null;

    /**
     * @var Managed[]
     */
    private $managed = [];

    /**
     * Config constructor.
     * @param Source $source
     */
    public function __construct(Source $source)
    {
        $this->source = $source;
    }

    /**
     * @param string $group
     * @param string $server
     * @param Managed $managed
     * @return Promised
     */
    public function attach(string $group, string $server, Managed $managed) : Promised
    {
        $this->managed[$named = $this->named($group, $server)] = $managed;

        $this->source->watching($named, static function (string $dsn = null) use ($named, $managed) {
            if (is_null($dsn)) {
                $managed->routing()->leave(
                    (new Endpoint(new Address('none://', $managed->port())))
                        ->relatedService($named)
                        ->assignID($named)
                );
            } else {
                $managed->routing()->join(
                    (new Endpoint(new Address($dsn, $managed->port())))
                        ->relatedService($named)
                        ->assignID($named)
                );
            }
        });

        return $managed->joined();
    }

    /**
     * @param string $group
     * @param string $server
     * @return Promised
     */
    public function detach(string $group, string $server) : Promised
    {
        return $this->managed[$this->named($group, $server)]->shutdown();
    }

    /**
     * @param string $group
     * @param string $server
     * @return string
     */
    private function named(string $group, string $server) : string
    {
        return sprintf('%s:%s', $group, $server);
    }
}
