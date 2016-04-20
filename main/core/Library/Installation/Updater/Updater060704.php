<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 2/18/16
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater060704 extends Updater
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        if (!$this->om->getRepository('ClarolineCoreBundle:Widget\Widget')->findOneByName('my_profile')) {
            $this->createWidget();
        }
    }

    private function createWidget()
    {
        $this->log('Creating my_profile widget...');
        $roles = $this->om->getRepository('ClarolineCoreBundle:Role')
            ->findAllPlatformRoles();
        $widget = new Widget();
        $widget->setName('my_profile');
        $widget->setConfigurable(false);
        $widget->setPlugin(null);
        $widget->setExportable(false);
        $widget->setDisplayableInDesktop(true);
        $widget->setDisplayableInWorkspace(false);

        foreach ($roles as $role) {
            $widget->addRole($role);
        }
        $this->om->persist($widget);
        $this->om->flush();
    }
}
