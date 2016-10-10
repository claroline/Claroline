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
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Persistence\ObjectManager;

/**
 * Platform widgets data fixture.
 */
class LoadWidgetData implements RequiredFixture
{
    /**
     * Loads the core widgets.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $roles = $manager->getRepository('ClarolineCoreBundle:Role')->findAllPlatformRoles();

        //name, isConfigurable, isDisplayableInDesktop, isDisplayableInWorkspace
        $items = [
            ['core_resource_logger', true, true, true],
            ['simple_text', true, true, true],
            ['my_workspaces', false, true, false],
            ['my_profile', false, true, false],
            ['resources_widget', true, true, true],
        ];

        foreach ($items as $item) {
            $widget = new Widget();
            $widget->setName($item[0]);
            $widget->setConfigurable($item[1]);
            $widget->setPlugin(null);
            $widget->setExportable(false);
            $widget->setIsDisplayableInDesktop($item[2]);
            $widget->setIsDisplayableInWorkspace($item[3]);

            foreach ($roles as $role) {
                $widget->addRole($role);
            }
            $manager->persist($widget);
        }
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
