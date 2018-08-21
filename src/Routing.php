<?php
/**
 * Cluster nodes routing
 * User: moyo
 * Date: 27/10/2017
 * Time: 4:03 PM
 */

namespace Carno\Cluster;

use Carno\Cluster\Contracts\Tags;
use Carno\Cluster\Routing\Property;
use Carno\Cluster\Routing\Selector;
use Carno\Net\Endpoint;
use Closure;

class Routing
{
    use Property;

    /**
     * event types
     */
    public const JOINING = 0xE0;
    public const LEAVING = 0xE1;

    /**
     * @var string
     */
    private $serviced = 'unassigned';

    /**
     * @var string[]
     */
    private $tags = [];

    /**
     * @var bool
     */
    private $strict = true;

    /**
     * @var Closure
     */
    private $watcher = null;

    /**
     * Routing constructor.
     * @param string $serviced
     * @param Closure $watcher
     */
    public function __construct(string $serviced, Closure $watcher)
    {
        $this->selector = new Selector($this->serviced = $serviced);
        $this->watcher = $watcher;
    }

    /**
     * @param string ...$tags
     * @return static
     */
    public function accepts(string ...$tags) : self
    {
        $this->tags = array_unique(array_merge($this->tags, $tags));
        return $this;
    }

    /**
     * @param bool $yes
     * @return static
     */
    public function strictly(bool $yes) : self
    {
        $this->strict = $yes;
        return $this;
    }

    /**
     * @param string ...$tags
     * @return Endpoint
     */
    public function picking(string ...$tags) : Endpoint
    {
        return $this->selector->picking(...$tags);
    }

    /**
     * @param Endpoint $endpoint
     */
    public function join(Endpoint $endpoint) : void
    {
        if (!$this->strict || $this->inTags($endpoint->getTags())) {
            $this->selector->classify($endpoint);
            logger('cluster')->info('Discovered new service', ['node' => (string)$endpoint]);
            ($this->watcher)(self::JOINING, $endpoint);
        } else {
            $this->nmWarning($endpoint, 'route.join');
        }
    }

    /**
     * @param Endpoint $endpoint
     */
    public function leave(Endpoint $endpoint) : void
    {
        if (!$this->strict || $this->inTags($endpoint->getTags())) {
            $this->selector->release($endpoint);
            logger('cluster')->info('Service is gone', ['node' => (string)$endpoint]);
            ($this->watcher)(self::LEAVING, $endpoint);
        } else {
            $this->nmWarning($endpoint, 'route.leave');
        }
    }

    /**
     * @param array $epTags
     * @return bool
     */
    private function inTags(array $epTags) : bool
    {
        foreach ($epTags ?: Tags::DEFAULT as $tag) {
            if (in_array($tag, $this->tags)) {
                return true;
            } elseif (substr($tag, 0, strlen(Tags::CMD)) === Tags::CMD) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Endpoint $endpoint
     * @param string $op
     */
    private function nmWarning(Endpoint $endpoint, string $op) : void
    {
        logger('cluster')->info(
            'Discovered service tags not match',
            [
                'action' => $op,
                'service' => $endpoint->service(),
                'expect' => implode(',', $this->tags ?? []),
                'found' => implode(',', $endpoint->getTags()),
            ]
        );
    }
}
