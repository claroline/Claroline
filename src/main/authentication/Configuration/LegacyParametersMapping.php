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

use Claroline\CoreBundle\Library\Configuration\LegacyParametersMappingInterface;

class LegacyParametersMapping implements LegacyParametersMappingInterface
{
    public function getMapping()
    {
        $parameters = [
            'redirect_after_login_option' => 'authentication.redirect_after_login_option',
            'redirect_after_login_url' => 'authentication.redirect_after_login_url',
        ];

        foreach (OauthConfiguration::resourceOwners() as $resourceOwner) {
            $resourceOwnerStr = str_replace(' ', '_', strtolower($resourceOwner));
            $parameters[$resourceOwnerStr.'_client_id'] = 'external_authentication.'.$resourceOwnerStr.'.client_id';
            $parameters[$resourceOwnerStr.'_client_secret'] = 'external_authentication.'.$resourceOwnerStr.'.client_secret';
            $parameters[$resourceOwnerStr.'_client_active'] = 'external_authentication.'.$resourceOwnerStr.'.client_active';
            $parameters[$resourceOwnerStr.'_client_force_reauthenticate'] = 'external_authentication.'.$resourceOwnerStr.'.client_force_reauthenticate';
            $parameters[$resourceOwnerStr.'_domain'] = 'external_authentication.'.$resourceOwnerStr.'.domain';
        }

        $parameters['generic_authorization_url'] = 'external_authentication.generic.authorization_url';
        $parameters['generic_access_token_url'] = 'external_authentication.generic.access_token_url';
        $parameters['generic_infos_url'] = 'external_authentication.generic.infos_url';
        $parameters['generic_scope'] = 'external_authentication.generic.scope';
        $parameters['generic_paths_login'] = 'external_authentication.generic.paths_login';
        $parameters['generic_paths_email'] = 'external_authentication.generic.paths_email';
        $parameters['generic_display_name'] = 'external_authentication.generic.display_name';

        return $parameters;
    }
}
