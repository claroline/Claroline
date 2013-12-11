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

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Widget\Widget;

class Updater020500
{
    private $container;
    private $logger;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function preUpdate()
    {
        $this->log('updating workspace...');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspaceManager = $this->container->get('claroline.manager.workspace_manager');
        $translator = $this->container->get('translator');
        $workspaces = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findAll();

        foreach ($workspaces as $workspace) {

            if ($workspace->isDisplayable() === null) {
                $workspace->setDisplayable(false);
            }

            if ($workspace->getSelfRegistration() === null) {
                $workspace->setSelfRegistration(false);
            }

            if ($workspace->getSelfUnregistration() === null) {
                $workspace->setSelfUnregistration(false);
            }

            $user = $workspaceManager->findPersonalUser($workspace);

            if ($user !== null) {
                $personalWorkspaceName = $translator->trans('personal_workspace', array(), 'platform') .
                ' - ' . $user->getUsername();
                $this->container->get('claroline.manager.workspace_manager')->rename($workspace, $personalWorkspaceName);
            }

            $em->persist($workspace);
            $em->flush();
        }

        $this->log('Adding agenda widget...');
        $widget = new Widget();
        $widget->setName('agenda');
        $widget->setConfigurable(false);
        $widget->setIcon('fake/icon/path');
        $widget->setPlugin(null);
        $widget->setExportable(false);
        $widget->setDisplayableInDesktop(true);
        $widget->setDisplayableInWorkspace(true);
        $em->persist($widget);

    }

    public function postUpdate()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $decoder = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneBy(array('name' => 'analytics'));
        if (!$decoder) {
            $wsTool = new Tool();
            $wsTool->setName('analytics');
            $wsTool->setClass('icon-bar-chart');
            $wsTool->setIsWorkspaceRequired(false);
            $wsTool->setIsDesktopRequired(false);
            $wsTool->setDisplayableInWorkspace(true);
            $wsTool->setDisplayableInDesktop(false);
            $wsTool->setExportable(false);
            $wsTool->setIsConfigurableInWorkspace(false);
            $wsTool->setIsConfigurableInDesktop(false);

            $em->persist($wsTool);
            $this->log("Adding 'analytics' tool in workspaces");
        } else {
            $this->log("The 'analytics' tool already exists");
        }

        $em->flush();
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }
}