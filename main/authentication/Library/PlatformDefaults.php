<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Library;

use Claroline\AuthenticationBundle\Model\Oauth\OauthConfiguration;
use Claroline\CoreBundle\Library\Configuration\ParameterProviderInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.configuration")
 */
class PlatformDefaults implements ParameterProviderInterface
{
    public function getDefaultParameters()
    {
        $parameters = [];

        foreach (OauthConfiguration::resourceOwners() as $resourceOwner) {
            $resourceOwnerStr = str_replace(' ', '_', strtolower($resourceOwner));
            $parameters['external_authentication'][$resourceOwnerStr]['client_id'] = null;
            $parameters['external_authentication'][$resourceOwnerStr]['client_secret'] = null;
            $parameters['external_authentication'][$resourceOwnerStr]['client_active'] = null;
            $parameters['external_authentication'][$resourceOwnerStr]['client_force_reauthenticate'] = null;
        }

        return $parameters;
    }
}
