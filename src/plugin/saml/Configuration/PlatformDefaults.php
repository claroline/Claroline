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
                //   - metadata      : the path to the metadata file (either URL or local files are allowed)
                //   - active        : enable or disable a single idp
                //   - label         : the label to display when displaying the login button
                //   - confirm       : a confirm text to display before redirecting to the IDP login page
                //   - email_domains : an array of email domains. If specified, only users with matching emails will be registered to idp groups and organization
                //   - conditions    : an array of expected saml response values (key is the field name). If specified, only users who match those values will be registered to groups and organization
                //   - organization  : An organization UUID to register users created from this idp
                //   - groups        : A list of groups UUID to register users created from this idp
                //   - mapping       : an associative array to know which saml props should be used for email, firstName and lastName at creation
                'idp' => [],
                // logout from idp when logout from claroline
                'logout' => true,
            ],
        ];
    }
}
