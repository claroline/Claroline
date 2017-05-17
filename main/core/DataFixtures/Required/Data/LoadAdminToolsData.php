<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required\Data;

use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Persistence\ObjectManager;

class LoadAdminToolsData implements RequiredFixture
{
    private $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $tools = [
            ['platform_parameters', 'cog'],
            ['user_management', 'user'],
            ['workspace_management', 'book'],
            ['registration_to_workspace', 'book'],
            ['desktop_and_home', 'home'],
            ['platform_logs', 'bars'],
            ['platform_analytics', 'bar-chart-o'],
            ['roles_management', 'users'],
            ['widgets_management', 'list-alt'],
            ['organization_management', 'institution'],
        ];

        foreach ($tools as $tool) {
            $entity = new AdminTool();
            $entity->setName($tool[0]);
            $entity->setClass($tool[1]);
            $manager->persist($entity);
        }

        $manager->flush();

        $this->container->get('claroline.manager.administration_manager')->addDefaultAdditionalActions();
    }
}
