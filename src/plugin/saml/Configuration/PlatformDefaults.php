<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SamlBundle\Configuration;

use Claroline\CoreBundle\Library\Configuration\ParameterProviderInterface;

class PlatformDefaults implements ParameterProviderInterface
{
    public function getDefaultParameters()
    {
        return [
            'saml' => [
                'active' => false,
                'entity_id' => 'claroline', // the sp name
                'reactivate_on_login' => false, // will automatically reactivate disabled users when they log through saml
                'credentials' => [], // the app certificates and secrets
                // The list of defined idp.
                // Array is indexed by IDPs entityId.
                // Each idp is an array with the following props :
                //   - metadata     : the path to the metadata file (either URL or local files are allowed)
                //   - active       : enable or disable a single idp
                //   - label        : the label to display when displaying the login button
                //   - organization : An organization UUID to register users created from this idp
                //   - groups       : A list of groups UUID to register users created from this idp
                'idp' => [],
                // logout from idp when logout from claroline
                'logout' => true,
            ],
        ];
    }
}
