<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Configuration;

use Claroline\CoreBundle\Library\Configuration\ParameterProviderInterface;

class PlatformDefaults implements ParameterProviderInterface
{
    public function getDefaultParameters()
    {
        $parameters = [
            'external_authentication' => [],
        ];

        foreach (OauthConfiguration::resourceOwners() as $resourceOwner) {
            $resourceOwnerStr = str_replace(' ', '_', strtolower($resourceOwner));
            $parameters['external_authentication'][$resourceOwnerStr]['client_id'] = null;
            $parameters['external_authentication'][$resourceOwnerStr]['client_secret'] = null;
            $parameters['external_authentication'][$resourceOwnerStr]['client_active'] = false;
            $parameters['external_authentication'][$resourceOwnerStr]['client_force_reauthenticate'] = false;
        }

        return $parameters;
    }
}
