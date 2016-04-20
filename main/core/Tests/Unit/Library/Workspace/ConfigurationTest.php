<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Workspace;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class ConfigurationTest extends MockeryTestCase
{
    /** @dataProvider rolesProvider */
    public function testCheckThrowsExceptionOnMissingRoles($roles, $isExeptionExpected)
    {
        if ($isExeptionExpected) {
            $this->setExpectedException('Claroline\CoreBundle\Library\Workspace\Exception\BaseRoleException');
        }

        $config = new Configuration(null, false);
        $config->checkRoles($roles);
    }

    public function rolesProvider()
    {
        $validMininal = array(
            'ROLE_WS_VISITOR' => 'visitor',
            'ROLE_WS_COLLABORATOR' => 'collaborator',
            'ROLE_WS_MANAGER' => 'manager',
        );

        $validOptional = array(
            'ROLE_WS_VISITOR' => 'visitor',
            'ROLE_WS_COLLABORATOR' => 'collaborator',
            'ROLE_WS_MANAGER' => 'manager',
            'ROLE_WS_ADDITIONAL' => 'new',
        );

        $missingMandatory = array(
            'ROLE_WS_VISITOR' => 'visitor',
            'ROLE_WS_ADDITIONAL' => 'new',
        );

        return array(
            array('roles' => $validMininal, 'isExceptionExpected' => false),
            array('roles' => $validOptional, 'isExceptionExpected' => false),
            array('roles' => $missingMandatory, 'isExceptionExpected' => true),
        );
    }
}
