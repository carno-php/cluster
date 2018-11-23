<?php
/**
 * Discovering via consul
 * User: moyo
 * Date: 16/11/2017
 * Time: 11:32 AM
 */

namespace Carno\Cluster\Discovery\Adaptors;

use Carno\Channel\Chan;
use Carno\Channel\Channel;
use Carno\Channel\Worker;
use Carno\Cluster\Discovery\Discovered;
use Carno\Cluster\Managed;
use Carno\Consul\Discovery;
use Carno\Consul\Types\Agent;
use Carno\Consul\Types\Router;
use function Carno\Coroutine\all;
use Carno\Promise\Promise;
use Carno\Promise\Promised;

class Consul implements Discovered
{
    /**
     * @var Agent
     */
    private $agent = null;

    /**
     * @var Managed[]
     */
    private $instances = [];

    /**
     * @var Chan[]
     */
    private $channels = [];

    /**
     * Connector constructor.
     * @param Agent $agent
     */
    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }

    /**
     * @param string $group
     * @param string $server
     * @param Managed $managed
     * @return Promised
     */
    public function attach(string $group, string $server, Managed $managed) : Promised
    {
        (new Discovery($this->agent))
            ->watching(
                $named = $this->serviced($group, $server),
                $this->channels[$named] = $notify = new Channel
            )
        ;

        new Worker($notify, function (array $routes) use ($named, $managed) {
            /**
             * @var Router[] $routes
             */

            foreach ($routes as $route) {
                if ($route->joined()) {
                    $managed->routing()->join($route->target());
                } elseif ($route->leaved()) {
                    $managed->routing()->leave($route->target());
                }
            }

            $managed->ready(true);
        });

        return $managed->ready(function () use ($named, $managed) {
            $this->instances[$named] = $managed;
        });
    }


    /**
     * @param string $group
     * @param string $server
     * @return Promised
     */
    public function detach(string $group, string $server) : Promised
    {
        $named = $this->serviced($group, $server);

        if (!isset($this->instances[$named])) {
            return Promise::rejected();
        }

        $managed = $this->instances[$named];
        unset($this->instances[$named]);

        $channel = $this->channels[$named];
        unset($this->channels[$named]);

        logger('cluster')->info('Service watcher has been closed', ['name' => $named]);

        $channel->close();

        return all($channel->closed(), $managed->shutdown());
    }

    /**
     * @param string $group
     * @param string $server
     * @return string
     */
    private function serviced(string $group, string $server) : string
    {
        return $group ? sprintf('%s:%s', $group, $server) : $server;
    }
}
