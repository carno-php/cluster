<?php
/**
 * Cluster resources manager
 * User: moyo
 * Date: 27/10/2017
 * Time: 2:48 PM
 */

namespace Carno\Cluster;

use Carno\Cluster\Chips\InitializeTips;
use Carno\Cluster\Discover\Discovered;
use function Carno\Coroutine\all;
use Carno\Promise\Promised;

class Resources
{
    use InitializeTips;

    /**
     * @var Discovered
     */
    private $discover = null;

    /**
     * @var bool
     */
    private $started = false;

    /**
     * @var array
     */
    private $paused = [];

    /**
     * @var array
     */
    private $loaded = [];

    /**
     * @var Promised[]
     */
    private $waits = [];

    /**
     * Resources constructor.
     * @param Discovered $discovered
     */
    public function __construct(Discovered $discovered)
    {
        $this->discover = $discovered;
    }

    /**
     * @return static
     */
    public function startup() : self
    {
        $this->started = true;
        while ($this->paused) {
            $this->initialize(...array_shift($this->paused));
        }
        return $this;
    }

    /**
     * @param string $group
     * @param string $server
     * @param Managed $managed
     */
    public function initialize(string $group, string $server, Managed $managed) : void
    {
        if (!$this->started) {
            $this->paused[] = func_get_args();
            return;
        }

        $sk = sprintf('%s:%s', $group ?: 'service', $server);

        if (isset($this->loaded[$sk])) {
            return;
        }

        $this->waits[$sk] = $wait = $this->discover->attach($group, $server, $managed);

        $wait->then(function () use ($sk) {
            unset($this->waits[$sk]);
        });

        $this->loaded[$sk] = [$group, $server];

        $this->waiting($sk, $managed);
    }

    /**
     * @return Promised
     */
    public function ready() : Promised
    {
        return all(...array_values($this->waits));
    }

    /**
     * @return Promised
     */
    public function release() : Promised
    {
        $pending = [];

        foreach ($this->loaded as $gss) {
            $pending[] = $this->discover->detach(...$gss);
        }

        return all(...$pending);
    }
}
