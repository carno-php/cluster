<?php
/**
 * Tags defined
 * User: moyo
 * Date: 2018/4/18
 * Time: 6:32 PM
 */

namespace Carno\Cluster\Contracts;

interface Tags
{
    // prefix of command tags
    public const CMD = '#';

    // master tag name
    public const MASTER = 'master';

    // slave tag name (for resources)
    public const SLAVE = 'slave';

    // default tags for endpoint without tags
    public const DEFAULT = [Tags::MASTER];
}
