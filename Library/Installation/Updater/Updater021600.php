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

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\ORM\EntityManager;

class Updater021600 extends Updater
{
    private $container;
    /** @var EntityManager */
    private $em;

    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    public function postUpdate()
    {
        $this->changeAnonymousToolPermissions();
        $this->repairWorkspaceWidgetInstances();
    }

    private function changeAnonymousToolPermissions()
    {
        $this->log('Updating tools for anonymous...');

        foreach (array('home', 'resource_manager', 'agenda') as $toolName) {
            $tool = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName($toolName);
            $tool->setIsAnonymousExcluded(false);
        }

        $this->em->flush();
    }

    private function repairWorkspaceWidgetInstances()
    {
        $this->log('Repairing workspace widget instances...');

        $dql = '
            SELECT wic, wi FROM ClarolineCoreBundle:Widget\WidgetHomeTabConfig wic
            JOIN wic.widgetInstance wi
            WHERE wic.type = \'workspace\'
            AND wi.workspace IS NULL
        ';
        $configs = $this->em->createQuery($dql)->getResult();

        foreach ($configs as $config) {
            $adminWidgetInstance = $config->getWidgetInstance();
            $workspaceWidgetInstance = new WidgetInstance();
            $workspaceWidgetInstance->setIsAdmin(false);
            $workspaceWidgetInstance->setIsDesktop(false);
            $workspaceWidgetInstance->setName($adminWidgetInstance->getName());
            $workspaceWidgetInstance->setWidget($adminWidgetInstance->getWidget());
            $workspaceWidgetInstance->setWorkspace($config->getWorkspace());
            $this->em->persist($workspaceWidgetInstance);
            $config->setWidgetInstance($workspaceWidgetInstance);
        }

        $this->em->flush();
    }
}
