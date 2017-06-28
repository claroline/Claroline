<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Invalid\NonYamlRoutingResource1;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

class InvalidNonYamlRoutingResource1 extends DistributionPluginBundle
{
    public function getRoutingResourcesPaths()
    {
        $ds = DIRECTORY_SEPARATOR;
        $nonYamlPath = __DIR__."{$ds}Resources{$ds}config{$ds}routing.foo";

        return $nonYamlPath;
    }
}
