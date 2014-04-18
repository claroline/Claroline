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

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;

class LoadAdminTools implements RequiredFixture
{
    public function load(ObjectManager $manager)
    {
        $tools = array(
            array('platform_parameters', 'icon-cog'),
            array('user_management', 'icon-user'),
            array('workspace_management', 'icon-book'),
            array('badges_management', 'icon-trophy'),
            array('registration_to_workspace', 'icon-book'),
            array('platform_plugins', 'icon-wrench'),
            array('home_tabs', 'icon-th-large'),
            array('desktop_tools', 'icon-pencil'),
            array('platform_logs', 'icon-reorder'),
            array('platform_analytics', 'icon-bar-chart'),
            array('roles_management', 'icon-group')
        );

        foreach ($tools as $tool) {
            $entity = new AdminTool();
            $entity ->setName($tool[0]);
            $entity ->setClass($tool[1]);
            $manager->persist($entity);
        }

        $manager->flush();
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}