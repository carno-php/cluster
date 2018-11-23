<?php
/**
 * Discovering via config
 * User: moyo
 * Date: 2018/8/13
 * Time: 11:59 AM
 */

namespace Carno\Cluster\Discovery\Adaptors;

use Carno\Cluster\Contracts\Tags;
use Carno\Cluster\Discovery\Discovered;
use Carno\Cluster\Managed;
use Carno\Config\Config as Source;
use Carno\Net\Address;
use Carno\Net\Endpoint;
use Carno\Net\Routing\Table;
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
     * @param Source $config
     */
    public function __construct(Source $config)
    {
        $this->source = $config;
    }

    /**
     * @param string $group
     * @param string $server
     * @param Managed $managed
     * @return Promised
     */
    public function attach(string $group, string $server, Managed $managed) : Promised
    {
        $table = new Table;

        $this->managed[$named = $this->named($group, $server)] = $managed;

        $this->source->watching($named, static function (string $dsn = null) use ($table, $named, $managed) {
            foreach ($table->reset(...self::endpoints($dsn ?? '', $named)) as $op) {
                $op->joined() && $managed->routing()->join($op->target());
                $op->leaved() && $managed->routing()->leave($op->target());
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

    /**
     * @param string $conf
     * @param string $named
     * @return Endpoint[]
     */
    private static function endpoints(string $conf, string $named) : array
    {
        $endpoints = [];

        foreach (explode("\n", $conf) as $expr) {
            $expr = preg_replace('!\s+!', ' ', $expr);

            $tags = Tags::DEFAULT;
            $dsn = $expr;

            if ($tsp = strpos($expr, ' ')) {
                $tags = explode(',', substr($expr, $tsp - strlen($expr) + 1));
                $dsn = substr($expr, 0, $tsp);
            }

            $endpoints[] = (new Endpoint(new Address($dsn)))
                ->relatedService($named)
                ->assignID(md5($expr))
                ->setTags(...$tags);
            ;
        }

        return $endpoints;
    }
}
