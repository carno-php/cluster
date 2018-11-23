<?php
/**
 * Classified API
 * User: moyo
 * Date: 2018/11/21
 * Time: 4:48 PM
 */

namespace Carno\Cluster\Classify;

use Carno\Cluster\Discovery\Discovered;

interface Classified
{
    /**
     * @param string $scene
     * @return Discovered
     */
    public function discovery(string $scene) : Discovered;

    /**
     * @param string $scene
     * @param Discovered $discovery
     */
    public function assigning(string $scene, Discovered $discovery) : void;
}
