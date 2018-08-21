<?php
/**
 * State signals
 * User: moyo
 * Date: 27/10/2017
 * Time: 4:02 PM
 */

namespace Carno\Cluster\Managed;

use Carno\Promise\Promise;
use Carno\Promise\Promised;

trait Signals
{
    /**
     * @var Promised[]
     */
    private $signals = [];

    /**
     * @return Promised
     */
    public function ready() : Promised
    {
        return $this->sigs(__METHOD__, ...func_get_args());
    }

    /**
     * @return Promised
     */
    public function joined() : Promised
    {
        return $this->sigs(__METHOD__, ...func_get_args());
    }

    /**
     * @param string $name
     * @param mixed $act
     * @return Promised
     */
    private function sigs(string $name, $act = null) : Promised
    {
        if (is_null($act)) {
            return $this->signals[$name] ?? $this->signals[$name] = Promise::deferred();
        }

        $state = $this->sigs($name);

        if (is_bool($act) && $act) {
            $state->pended() && $state->resolve();
        } elseif (is_callable($act)) {
            $state->then($act);
        }

        return $state;
    }
}
