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

use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Persistence\ObjectManager;

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
        $role = $manager->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_WS_CREATOR');

        /** @var AdminTool $tool */
        $tool = $manager->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findOneByName('workspace_management');

        $tool->addRole($role);
        $this->container->get('claroline.persistence.object_manager')->persist($tool);
        $this->container->get('claroline.persistence.object_manager')->flush();
    }
}
