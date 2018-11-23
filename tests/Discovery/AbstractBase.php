<?php
/**
 * Tests base
 * User: moyo
 * Date: 2018/11/22
 * Time: 3:24 PM
 */

namespace Carno\Cluster\Tests\Discovery;

use Carno\Cluster\Classify\Selector;
use Carno\Cluster\Discovery\Adaptors\Config as Discover;
use Carno\Cluster\Managed;
use Carno\Cluster\Resources;
use Carno\Config\Config;
use PHPUnit\Framework\TestCase;

abstract class AbstractBase extends TestCase
{
    /**
     * @param string $scene
     * @param string $group
     * @param string $server
     * @param Managed $mocked
     * @return Config
     */
    protected function newConfDSV(string $scene, string $group, string $server, Managed $mocked) : Config
    {
        $resources = new Resources($classify = new Selector);

        $classify->assigning($scene, $discovery = new Discover($source = new Config));

        $resources->initialize($scene, $group, $server, $mocked);
        $resources->startup();

        return $source;
    }
}
