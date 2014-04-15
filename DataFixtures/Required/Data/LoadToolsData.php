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
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;

class LoadToolsData implements RequiredFixture
{
    public function load(ObjectManager $manager)
    {
        $tools = array(
            array('home', 'icon-home', false, false, true, true, true, false, false, false, true),
            array('parameters', 'icon-cog', false, false, true, true, false, false, false, true, true),
            array('resource_manager', 'icon-folder-open', false, false, true, true, true, true, false, false, true),
            array('agenda', 'icon-calendar', false, false, true, true, false, false, false, false, true),
            array('logs', 'icon-list', false, false, true, false, false, false, false, false, true),
            array('analytics', 'icon-bar-chart', false, false, true, false, false, false, false, false, true),
            array('users', 'icon-user', true, false, true, false, false, false, false, false, true),
            array('badges', 'icon-trophy', false, false, true, false, false, false, false, false, true),
            array('my_badges', 'icon-trophy', false, false, true, false, false, false, false, false, true)
        );

        foreach ($tools as $tool) {
            $entity = new Tool();
            $entity->setName($tool[0]);
            $entity->setClass($tool[1]);
            $entity->setIsWorkspaceRequired($tool[2]);
            $entity->setIsDesktopRequired($tool[3]);
            $entity->setDisplayableInWorkspace($tool[4]);
            $entity->setDisplayableInDesktop($tool[5]);
            $entity->setExportable($tool[6]);
            $entity->setIsConfigurableInWorkspace($tool[7]);
            $entity->setIsConfigurableInDesktop($tool[8]);
            $entity->setIsLockedForAdmin($tool[9]);
            $entity->setIsAnonymousExcluded($tool[10]);

            $manager->persist($entity);
        }
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
