<?php
/**
 * Default ports provider
 * User: moyo
 * Date: 17/11/2017
 * Time: 11:18 AM
 */

namespace Carno\Cluster\Managed;

trait Ports
{
    /**
     * @var int
     */
    protected $port = 80;

    /**
     * @return int
     */
    public function port() : int
    {
        return $this->port;
    }
}
