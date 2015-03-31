<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater040801 extends Updater
{
    private $container;
    private $om;

    /**
     * @var \Claroline\CoreBundle\Manager\ToolManager
     */
    private $toolManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->toolManager = $container->get('claroline.manager.tool_manager');
    }

    public function postUpdate()
    {
        $this->initializeAdminDesktopMenuConfiguration();
        $this->showDesktopParametersToolForAllUsers();
        $this->showDesktopResourceManagerForAllUsers();
    }

    private function initializeAdminDesktopMenuConfiguration()
    {
        $this->log('Initializing default desktop menu configuration...');
        $parametersTool = $this->toolManager->getOneToolByName('parameters');
        $resourcesTool = $this->toolManager->getOneToolByName('resource_manager');
        $messageTool = $this->toolManager->getOneToolByName('message');

        $this->om->startFlushSuite();

        if (!is_null($parametersTool)) {
            $paramOt = $this->toolManager->getOneAdminOrderedToolByToolAndType($parametersTool);

            if (is_null($paramOt)) {
                $paramOt = new OrderedTool();
                $paramOt->setTool($parametersTool);
                $paramOt->setType(0);
                $paramOt->setOrder(1);
                $paramOt->setLocked(false);
                $paramOt->setName($parametersTool->getName());
            }
            $paramOt->setVisibleInDesktop(true);
            $this->om->persist($paramOt);
        }

        if (!is_null($resourcesTool)) {
            $resourceOt = $this->toolManager->getOneAdminOrderedToolByToolAndType($resourcesTool);

            if (is_null($resourceOt)) {
                $resourceOt = new OrderedTool();
                $resourceOt->setTool($resourcesTool);
                $resourceOt->setType(0);
                $resourceOt->setOrder(2);
                $resourceOt->setLocked(false);
                $resourceOt->setName($resourcesTool->getName());
            }
            $resourceOt->setVisibleInDesktop(true);
            $this->om->persist($resourceOt);
        }

        if (!is_null($messageTool)) {
            $messageOt = $this->toolManager->getOneAdminOrderedToolByToolAndType($messageTool);

            if (is_null($messageOt)) {
                $messageOt = new OrderedTool();
                $messageOt->setTool($messageTool);
                $messageOt->setType(0);
                $messageOt->setOrder(3);
                $messageOt->setLocked(false);
                $messageOt->setName($messageTool->getName());
            }
            $messageOt->setVisibleInDesktop(true);
            $this->om->persist($messageOt);
        }
        $this->om->endFlushSuite();
    }

    private function showDesktopParametersToolForAllUsers()
    {
        $this->log('Activating desktop parameters ordered tools for all users...');
        $tool = $this->toolManager->getOneToolByName('parameters');

        if (!is_null($tool)) {
            $this->toolManager->createOrderedToolByToolForAllUsers($this->logger, $tool);
        }
    }

    private function showDesktopResourceManagerForAllUsers()
    {
        $this->log('Activating desktop resource manager ordered tools for all users...');
        $tool = $this->toolManager->getOneToolByName('resource_manager');

        if (!is_null($tool)) {
            $this->toolManager->createOrderedToolByToolForAllUsers($this->logger, $tool);
        }
    }
}
