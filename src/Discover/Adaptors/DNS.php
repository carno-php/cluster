<?php
/**
 * Discovering via dns
 * User: moyo
 * Date: 16/11/2017
 * Time: 11:35 AM
 */

namespace Carno\Cluster\Discover\Adaptors;

use Carno\Cluster\Discover\Discovered;
use Carno\Cluster\Managed;
use function Carno\Coroutine\go;
use Carno\DNS\DNS as NSR;
use Carno\DNS\Exception\ResolvingException;
use Carno\DNS\Result;
use Carno\Net\Address;
use Carno\Net\Endpoint;
use Carno\Promise\Promised;

class DNS implements Discovered
{
    /**
     * @var string
     */
    private $suffix = '';

    /**
     * @var Managed[]
     */
    private $managed = [];

    /**
     * DNS constructor.
     * @param string $suffix
     */
    public function __construct(string $suffix = '')
    {
        $this->suffix = $suffix;
    }

    /**
     * @param string $group
     * @param string $server
     * @param Managed $managed
     * @return Promised
     */
    public function attach(string $group, string $server, Managed $managed) : Promised
    {
        $this->managed[$domain = $this->domain($server)] = $managed;

        go(static function () use ($domain, $managed) {
            try {
                /**
                 * @var Result $resolved
                 */
                $resolved = yield NSR::resolve($domain);
                foreach ($resolved->iterator() as $host) {
                    $managed->routing()->join(
                        (new Endpoint(new Address($host, $managed->port())))
                            ->relatedService($domain)
                            ->assignID(ip2long($host))
                    );
                }
            } catch (ResolvingException $e) {
                logger('cluster')->warning('DNS resolve failed', ['domain' => $domain, 'ec' => get_class($e)]);
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
        return $this->managed[$this->domain($server)]->shutdown();
    }

    /**
     * @param string $server
     * @return string
     */
    private function domain(string $server) : string
    {
        return $server . $this->suffix;
    }
}
