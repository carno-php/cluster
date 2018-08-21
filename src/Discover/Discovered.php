<?php
/**
 * Discover interface
 * User: moyo
 * Date: 16/11/2017
 * Time: 11:52 AM
 */

namespace Carno\Cluster\Discover;

use Carno\Cluster\Managed;
use Carno\Promise\Promised;

interface Discovered
{
    /**
     * @param string $group
     * @param string $server
     * @param Managed $managed
     * @return Promised
     */
    public function attach(string $group, string $server, Managed $managed) : Promised;

    /**
     * @param string $group
     * @param string $server
     * @return Promised
     */
    public function detach(string $group, string $server) : Promised;
}
