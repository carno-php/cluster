<?php
/**
 * Config discovery test
 * User: moyo
 * Date: 2018/11/21
 * Time: 7:42 PM
 */

namespace Carno\Cluster\Tests\Discovery;

use Carno\Cluster\Classify\Scenes;
use Carno\Cluster\Tests\Utils\MockedResource;

class ConfigTest extends AbstractBase
{
    public function testDiscovery()
    {
        $conf = $this->newConfDSV(Scenes::RESOURCE, 'redis', 's1', $mock = new MockedResource);

        $rsk = 'redis:s1';

        $conf->set($rsk, $h1 = 'redis://host1.svc:111');
        $this->assertEquals(1, count($mock->endpoints()));

        $conf->set($rsk, implode("\n", [$h1, $h2 = 'redis://host2.svc:222 master']));
        $this->assertEquals(2, count($mock->endpoints()));

        $conf->set($rsk, implode("\n", [$h1, $h2, $h3 = 'redis://host3.svc:333 slave']));
        $this->assertEquals(3, count($mock->endpoints()));

        $conf->set($rsk, implode("\n", [$h2, $h3]));
        $this->assertEquals(2, count($mock->endpoints()));

        $conf->set($rsk, $h1);
        $this->assertEquals(1, count($mock->endpoints()));
    }
}
