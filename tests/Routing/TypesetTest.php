<?php
/**
 * Typeset test
 * User: moyo
 * Date: 2019-01-21
 * Time: 11:09
 */

namespace Carno\Cluster\Tests;

use Carno\Cluster\Routing\Typeset;
use Carno\Cluster\Tests\Utils\RTyped1;
use Carno\Cluster\Tests\Utils\RTyped2;
use Carno\Net\Address;
use Carno\Net\Endpoint;
use PHPUnit\Framework\TestCase;

class TypesetTest extends TestCase
{
    public function testOps()
    {
        $ts = new Typeset;

        $r1 = new RTyped1;
        $r2 = new RTyped2;

        $ep = new Endpoint(new Address(':0'));

        $ts->extend($r1)->extend($r2);

        $ts->classify('t1', $ep);

        $this->assertEmpty($ts->picked('t2'));
        $this->assertEquals($ep->id(), $ts->picked('t1')[0]->id());

        $ts->classify('t2', $ep);

        $this->assertNotEmpty($ts->picked('t1'));
        $this->assertEquals($ep->id(), $ts->picked('t2')[0]->id());

        $this->assertEquals(1, count($ts->picked('t1', 't2')));

        $ts->forget(RTyped1::class);

        $this->assertEmpty($ts->picked('t1'));
        $this->assertEquals(1, count($ts->picked('t2')));

        $ts->release($ep);

        $this->assertEmpty($ts->picked('t1'));
        $this->assertEmpty($ts->picked('t2'));

        $this->assertTrue($r1->available());
        $this->assertFalse($r2->available());

        $ts->extend($r1);
        $ts->release($ep);
        $this->assertFalse($r1->available());
    }
}
