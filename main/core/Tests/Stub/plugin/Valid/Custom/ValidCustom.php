<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Valid\Custom;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

/**
 * Plugin overriding all the ClarolinePlugin methods.
 */
class ValidCustom extends DistributionPluginBundle
{
    public function getRoutingResourcesPaths()
    {
        $ds = DIRECTORY_SEPARATOR;
        $commonPath = $this->getPath().$ds.'Resources'.$ds.'config';
        $firstPath = $commonPath.$ds.'routing'.$ds.'routing.yml';
        $secondPath = $commonPath.$ds.'special_routing'.$ds.'routing.yml';

        return [$firstPath, $secondPath];
    }

    public function getRoutingPrefix()
    {
        return 'custom_routing_prefix';
    }

    public function getDescriptionTranslationKey()
    {
        return 'Custom description translation key';
    }
}
