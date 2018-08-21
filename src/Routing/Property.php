<?php
/**
 * Routing property
 * User: moyo
 * Date: 2018/8/9
 * Time: 11:19 PM
 */

namespace Carno\Cluster\Routing;

trait Property
{
    /**
     * @var Selector
     */
    private $selector = null;

    /**
     * @return bool
     */
    public function available() : bool
    {
        return $this->selector->available();
    }

    /**
     * @return bool
     */
    public function clustered() : bool
    {
        return $this->selector->clustered();
    }

    /**
     * @return Typeset
     */
    public function typeset() : Typeset
    {
        return $this->selector->typeset();
    }
}
