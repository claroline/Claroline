<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\PostInstall\Data;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AdminTool;

class PostLoadRolesData implements RequiredFixture
{
    private $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $om = $this->container->get('claroline.persistence.object_manager');

        /** @var Role $role */
        $wscreator = $manager->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_WS_CREATOR');

        /** @var AdminTool $tool */
        $wsmanagement = $manager->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findOneByName('workspace_management');

        $wsmanagement->addRole($wscreator);
        $om->persist($wsmanagement);

        /** @var Role $role */
        $adminOrganization = $manager->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_ADMIN_ORGANIZATION');

        /** @var AdminTool $tool */
        $usermanagement = $manager->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findOneByName('user_management');
        $workspacemanagement = $manager->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findOneByName('workspace_management');
        $usermanagement->addRole($adminOrganization);
        $workspacemanagement->addRole($adminOrganization);
        $om->persist($usermanagement);
        $om->persist($workspacemanagement);
        $om->flush();
    }
}
