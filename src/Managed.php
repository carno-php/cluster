<?php
/**
 * Abstract managed cluster
 * User: moyo
 * Date: 25/10/2017
 * Time: 11:28 AM
 */

namespace Carno\Cluster;

use Carno\Cluster\Managed\Adaptor;
use Carno\Cluster\Managed\Operator;
use Carno\Cluster\Managed\Ports;
use Carno\Cluster\Managed\Router;
use Carno\Cluster\Managed\Signals;

abstract class Managed
{
    use Operator, Signals, Router, Adaptor, Ports;

    /**
     * @var string
     */
    protected $type = 'none';

    /**
     * @var string
     */
    protected $server = 'unassigned';
}
