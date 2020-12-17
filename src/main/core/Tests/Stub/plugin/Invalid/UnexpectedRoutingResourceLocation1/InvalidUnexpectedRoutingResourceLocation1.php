<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Invalid\UnexpectedRoutingResourceLocation1;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

class InvalidUnexpectedRoutingResourceLocation1 extends DistributionPluginBundle
{
    public function getRoutingResourcesPaths()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Misc{$ds}misplaced_routing_file.yml";

        return $path;
    }
}
