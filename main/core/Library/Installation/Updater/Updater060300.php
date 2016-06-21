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

use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater060300 extends Updater
{
    private $container;
    private $om;
    private $adminToolRepo;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->adminToolRepo = $this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool');
    }

    public function postUpdate()
    {
        $this->om->startFlushSuite();
        $this->createWidgetsManagementAdminTool();
        $this->initializeWidgetsRoles();
        $this->om->endFlushSuite();
    }

    private function createWidgetsManagementAdminTool()
    {
        $this->log('Creating Widgets management admin tool...');
        $widgetsTools = $this->adminToolRepo->findByName('widgets_management');

        if (count($widgetsTools) === 0) {
            $widgetsTool = new AdminTool();
            $widgetsTool->setName('widgets_management');
            $widgetsTool->setClass('list-alt');
            $this->om->persist($widgetsTool);
            $this->om->flush();
        }
    }

    private function initializeWidgetsRoles()
    {
        $this->log('Initializing roles for Widgets...');
        $widgets = $this->om->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findAll();
        $roles = $this->om->getRepository('ClarolineCoreBundle:Role')
            ->findAllPlatformRoles();

        foreach ($widgets as $widget) {
            foreach ($roles as $role) {
                $widget->addRole($role);
            }
            $this->om->persist($widget);
        }
        $this->om->flush();
    }
}
