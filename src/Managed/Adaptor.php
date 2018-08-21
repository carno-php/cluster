<?php
/**
 * Connector API
 * User: moyo
 * Date: 30/10/2017
 * Time: 3:00 PM
 */

namespace Carno\Cluster\Managed;

use Carno\Net\Endpoint;
use Carno\Promise\Promised;

trait Adaptor
{
    /**
     * @param Endpoint $endpoint
     * @return mixed
     */
    abstract protected function connecting(Endpoint $endpoint);

    /**
     * @param mixed $connected
     * @return Promised
     */
    abstract protected function disconnecting($connected) : Promised;
}
