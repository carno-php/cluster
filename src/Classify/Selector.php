<?php
/**
 * Classify selector
 * User: moyo
 * Date: 2018/11/21
 * Time: 4:57 PM
 */

namespace Carno\Cluster\Classify;

use Carno\Cluster\Discovery\Discovered;
use Carno\Cluster\Exception\UnassignedDiscoveryException;

class Selector implements Classified
{
    /**
     * @var Discovered
     */
    private $discoveries = [];

    /**
     * @param string $scene
     * @return Discovered
     */
    public function discovery(string $scene) : Discovered
    {
        if (isset($this->discoveries[$scene])) {
            return $this->discoveries[$scene];
        }
        throw new UnassignedDiscoveryException;
    }

    /**
     * @param string $scene
     * @param Discovered $discovery
     */
    public function assigning(string $scene, Discovered $discovery) : void
    {
        $this->discoveries[$scene] = $discovery;
    }
}
