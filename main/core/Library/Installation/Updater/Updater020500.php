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
use Claroline\InstallationBundle\Updater\Updater;

class Updater020500 extends Updater
{
    private $container;
    private $om;

    public function __construct($container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateWorkspaceNames();
        $this->updateMimeTypes();
        $this->addAgendaWidget();
        $this->addAnalyticsWorkspaceTool();
    }

    private function updateWorkspaceNames()
    {
        $this->log('Updating workspace names...');

        $workspaceManager = $this->container->get('claroline.manager.workspace_manager');
        $translator = $this->container->get('translator');
        $workspaces = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findAll();
        $trans = $translator->trans('personal_workspace', array(), 'platform');
        $this->om->startFlushSuite();

        for ($i = 0, $count = count($workspaces); $i < $count; ++$i) {
            $workspace = $workspaces[$i];

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
                $personalWorkspaceName = $trans.$user->getUsername();
                $this->log('Renaming '.$personalWorkspaceName);
                $workspaceManager->rename($workspace, $personalWorkspaceName);
            }

            $this->om->persist($workspace);

            if ($i % 200 === 0) {
                $this->om->endFlushSuite();
                $this->om->startFlushSuite();
            }
        }

        $this->om->endFlushSuite();
    }

    private function updateMimeTypes()
    {
        $this->log('Setting known default file mime types...');

        $guesser = $this->container->get('claroline.utilities.mime_type_guesser');
        $fileType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('file');
        $fileNodes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findBy(
            array('resourceType' => $fileType)
        );

        for ($i = 0, $count = count($fileNodes); $i < $count; ++$i) {
            if ($fileNodes[$i]->getMimeType() === null) {
                $nameParts = explode('.', $fileNodes[$i]->getName());
                $extension = array_pop($nameParts);
                $fileNodes[$i]->setMimeType($guesser->guess($extension));
                $this->om->persist($fileNodes[$i]);

                if ($i % 200 === 0) {
                    $this->om->flush();
                }
            }
        }
    }

    private function addAgendaWidget()
    {
        $existingWidget = $this->om->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findOneByName('agenda');

        if (!$existingWidget) {
            $newWidget = new Widget();
            $newWidget->setName('agenda');
            $newWidget->setConfigurable(false);
            $newWidget->setIcon('fake/icon/path');
            $newWidget->setPlugin(null);
            $newWidget->setExportable(false);
            $newWidget->setDisplayableInDesktop(true);
            $newWidget->setDisplayableInWorkspace(true);

            $this->om->persist($newWidget);
            $this->log("'agenda' widget added.");
        } else {
            $this->log("The 'agenda' widget already exists");
        }

        $this->om->flush();
    }

    private function addAnalyticsWorkspaceTool()
    {
        $decoder = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(array('name' => 'analytics'));

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

            $this->om->persist($wsTool);
            $this->log("Adding 'analytics' tool in workspaces");
        } else {
            $this->log("The 'analytics' tool already exists");
        }

        $this->om->flush();
    }
}
