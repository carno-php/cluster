<?php
/**
 * Initialize tips for resources
 * User: moyo
 * Date: 2018/4/11
 * Time: 11:15 AM
 */

namespace Carno\Cluster\Chips;

use Carno\Cluster\Managed;
use Carno\Timer\Timer;

trait InitializeTips
{
    /**
     * @var array
     */
    private $checker = [];

    /**
     * @param string $sk
     * @param Managed $managed
     */
    private function waiting(string $sk, Managed $managed) : void
    {
        $oid = spl_object_id($managed);

        $this->checker[$oid] = Timer::loop(30000, function () use ($sk) {
            logger('cluster')->notice('Now is waiting', ['resource' => $sk]);
        });

        $managed->joined(function () use ($oid) {
            Timer::clear($this->checker[$oid]);
        });
    }
}
