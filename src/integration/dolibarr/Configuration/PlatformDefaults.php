<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DolibarrBundle\Configuration;

use Claroline\CoreBundle\Library\Configuration\ParameterProviderInterface;

class PlatformDefaults implements ParameterProviderInterface
{
    public function getDefaultParameters()
    {
        return [
            'dolibarr' => [
                // the dolibarr instance url
                'url' => null,
                // the dolibarr api key
                'key' => null,
                // we will sync all the trainings from dolibarr with these statuses
                'trainingStatuses' => [],
                // the workspace mode used to create ws for synced trainings
                'workspaceModel' => null,
            ],
        ];
    }
}
