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
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\Tool\PwsToolConfig;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;
use Claroline\CoreBundle\Entity\Resource\PwsRightsManagementAccess;

class LoadToolsData implements RequiredFixture
{
    public function load(ObjectManager $manager)
    {
        $tools = array(
            array('home', 'home', false, false, true, true, true, false, false, false, false),
            array('parameters', 'cogs', false, false, true, true, false, false, false, true, true),
            array('resource_manager', 'folder-open', false, false, true, true, true, true, false, false, false),
            array('logs', 'list', false, false, true, false, false, false, false, false, true),
            array('analytics', 'bar-chart-o', false, false, true, false, false, false, false, false, true),
            array('users', 'user', true, false, true, false, false, false, false, false, true),
            array('my_contacts', 'users', false, false, false, true, false, false, false, false, true)
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
            $this->createPersonalWorkspaceToolConfig($manager, $entity);

            if ($tool[0] === 'parameters' || $tool[0] === 'resource_manager' || $tool[0] === 'my_contacts') {
                $this->createAdminOrderedTool($manager, $entity);
            }
        }

        $this->updatePersonalWorkspaceResourceRightsConfig($manager);
        $manager->flush();
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

    private function createPersonalWorkspaceToolConfig(ObjectManager $manager, Tool $tool)
    {
        $roleUser = $manager->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER');
        $pwc = new PwsToolConfig();
        $pwc->setTool($tool);
        $pwc->setRole($roleUser);
        $pwc->setMask(3);
        $manager->persist($pwc);
    }

    private function updatePersonalWorkspaceResourceRightsConfig(ObjectManager $manager)
    {
        $roleUser = $manager->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER');
        $config = new PwsRightsManagementAccess();
        $config->setRole($roleUser);
        $config->setIsAccessible(true);
        $manager->persist($config);
        $manager->flush();
    }

    private function createAdminOrderedTool(ObjectManager $manager, Tool $tool)
    {
        $orderedTool = new OrderedTool();
        $orderedTool->setTool($tool);
        $orderedTool->setType(0);
        $orderedTool->setOrder(1);
        $orderedTool->setLocked(false);
        $orderedTool->setName($tool->getName());
        $orderedTool->setVisibleInDesktop(true);
        $manager->persist($orderedTool);
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
