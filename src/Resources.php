<?php
/**
 * Cluster resources manager
 * User: moyo
 * Date: 27/10/2017
 * Time: 2:48 PM
 */

namespace Carno\Cluster;

use Carno\Cluster\Chips\InitializeTips;
use Carno\Cluster\Classify\Classified;
use function Carno\Coroutine\all;
use Carno\Promise\Promised;

class Resources
{
    use InitializeTips;

    /**
     * @var Classified
     */
    private $classify = null;

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
     * @param Classified $classify
     */
    public function __construct(Classified $classify)
    {
        $this->classify = $classify;
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
     * @param string $scene
     * @param string $group
     * @param string $server
     * @param Managed $managed
     */
    public function initialize(string $scene, string $group, string $server, Managed $managed) : void
    {
        if (!$this->started) {
            $this->paused[] = func_get_args();
            return;
        }

        $sk = sprintf('%s:%s', $group ?: 'service', $server);

        if (isset($this->loaded[$sk])) {
            return;
        }

        $this->waits[$sk] = $wait = $this->classify->discovery($scene)->attach($group, $server, $managed);

        $wait->then(function () use ($sk) {
            unset($this->waits[$sk]);
        });

        $this->loaded[$sk] = [$scene, $group, $server];

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
            $pending[] = $this->classify->discovery(array_shift($gss))->detach(...$gss);
        }

        return all(...$pending);
    }
}
