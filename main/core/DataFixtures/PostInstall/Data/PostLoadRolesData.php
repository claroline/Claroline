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
        /** @var Role $role */
        $adminOrganization = $manager->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_ADMIN_ORGANIZATION');

        /** @var AdminTool $tool */
        $userManagement = $manager->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findOneByName('community');
        $userManagement->addRole($adminOrganization);

        $manager->persist($userManagement);
        $manager->flush();
    }
}
