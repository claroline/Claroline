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
        $this->log('updating workspaced names...');
        $this->log('This operation may take a while.');
        $om = $this->container->get('claroline.persistence.object_manager');
        $workspaceManager = $this->container->get('claroline.manager.workspace_manager');
        $translator = $this->container->get('translator');
        $workspaces = $om->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findAll();
        $batchSize = 200;
        $i = 0;
        $trans = $translator->trans('personal_workspace', array(), 'platform');
        $om->startFlushSuite();

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
                $personalWorkspaceName = $trans . ' - ' . $user->getUsername();
                $this->container->get('claroline.manager.workspace_manager')->rename($workspace, $personalWorkspaceName);
            }

            $this->log('Renaming ' . $personalWorkspaceName);
            $om->persist($workspace);
            $i++;

            if ($i % $batchSize === 0) {
                $this->log('flushing database...');
                $om->endFlushSuite();
                $om->startFlushSuite();
            }
        }

        $om->endFlushSuite();

//        $this->log('Adding agenda widget...');
//        $widget = new Widget();
//        $widget->setName('agenda');
//        $widget->setConfigurable(false);
//        $widget->setIcon('fake/icon/path');
//        $widget->setPlugin(null);
//        $widget->setExportable(false);
//        $widget->setDisplayableInDesktop(true);
//        $widget->setDisplayableInWorkspace(true);
//        $om->persist($widget);
//        $om->flush();

        $this->log('Setting known default file mime types...');
        $this->log('This operation may take a while');

        $fileType = $om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('file');
        $fileNodes = $om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findBy(array('resourceType' => $fileType));

        foreach ($fileNodes as $node) {
            if  ($node->getMimeType() === null) {
                $name = $node->getName();
                $extension = array_pop(explode('.', $name));
                $node->setMimeType($this->container->get('claroline.utilities.mime_type_guesser')->guess($extension));
                $om->persist($node);

                if ($i % $batchSize === 0) {
                    $om->flush();
                }
            }
        }

        $om->flush();
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