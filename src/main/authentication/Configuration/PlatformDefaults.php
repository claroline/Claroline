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
    const DEFAULT_REDIRECT_OPTION = 'DESKTOP';

    const REDIRECT_OPTIONS = [
        'DESKTOP' => 'DESKTOP',
        'LAST' => 'LAST',
        'URL' => 'URL',
        'WORKSPACE_TAG' => 'WORKSPACE_TAG',
    ];

    public function getDefaultParameters()
    {
        $parameters = [
            'authentication' => [
                'help' => null,
                'changePassword' => true,
                'redirect_after_login_option' => self::DEFAULT_REDIRECT_OPTION,
                'redirect_after_login_url' => null,
                'direct_third_party' => false,
            ],
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
