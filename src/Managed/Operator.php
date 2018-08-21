<?php
/**
 * Operator cmds
 * User: moyo
 * Date: 27/10/2017
 * Time: 3:19 PM
 */

namespace Carno\Cluster\Managed;

use Carno\Promise\Promised;

trait Operator
{
    /**
     * @return Promised
     */
    public function shutdown() : Promised
    {
        return $this->releasing();
    }
}
