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
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;

class LoadToolsData implements RequiredFixture
{
    public function load(ObjectManager $manager)
    {
        $tools = array(
            array('home', 'home', false, false, true, true, true, false, false, false, false),
            array('parameters', 'cog', false, false, true, true, false, false, false, true, true),
            array('resource_manager', 'folder-open', false, false, true, true, true, true, false, false, false),
            array('agenda', 'calendar', false, false, true, true, false, false, false, false, false),
            array('logs', 'list', false, false, true, false, false, false, false, false, true),
            array('analytics', 'bar-chart-o', false, false, true, false, false, false, false, false, true),
            array('users', 'user', true, false, true, false, false, false, false, false, true),
            array('badges', 'trophy', false, false, true, false, false, false, false, false, true),
            array('my_badges', 'trophy', false, false, true, false, false, false, false, false, true)
            //array('learning_profil', 'graduation-cap', true, false, true, false, false, false, false, false, true),
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
            $this->createToolMaskDecoders($manager, $entity);
        }
    }

    private function createToolMaskDecoders(ObjectManager $manager, Tool $tool)
    {
        foreach (ToolMaskDecoder::$defaultActions as $action) {
            $decoder = new ToolMaskDecoder();
            $decoder->setTool($tool);
            $decoder->setName($action);
            $decoder->setValue(ToolMaskDecoder::$defaultValues[$action]);
            $decoder->setGrantedIconClass(ToolMaskDecoder::$defaultGrantedIconClass[$action]);
            $decoder->setDeniedIconClass(ToolMaskDecoder::$defaultDeniedIconClass[$action]);
            $manager->persist($decoder);
        }
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
