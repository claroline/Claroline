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

use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater080000 extends Updater
{
    private $container;

    public function __construct(ContainerInterface $container, $logger)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->logger = $logger;
    }

    public function postUpdate()
    {
        $resourceTypes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll(false);
        $this->log('Enabling resource types...');

        foreach ($resourceTypes as $resourceType) {
            $this->log("Enabling {$resourceType->getName()}");
            $resourceType->setIsEnabled(true);
            $this->om->persist($resourceType);
        }

        $this->om->flush();
        $this->setDisabledUserAsRemoved();

        if (!$this->om->getRepository('ClarolineCoreBundle:Widget\Widget')->findOneByName('resources_widget')) {
            $this->createResourcesWidget();
        }

        $this->log('Resetting facet order...');
        $this->container->get('claroline.manager.facet_manager')->resetFacetOrder();
    }

    private function setDisabledUserAsRemoved()
    {
        $this->log('Updating database for removed users...');
        $this->om->getRepository('ClarolineCoreBundle:User')->createQueryBuilder('u')
            ->update()
            ->set('u.isRemoved', true)
            ->where('u.isEnabled = false')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createResourcesWidget()
    {
        $this->log('Creating resources_widget widget...');
        $roles = $this->om->getRepository('ClarolineCoreBundle:Role')->findAllPlatformRoles();
        $widget = new Widget();
        $widget->setName('resources_widget');
        $widget->setConfigurable(true);
        $widget->setPlugin(null);
        $widget->setExportable(false);
        $widget->setDisplayableInDesktop(true);
        $widget->setDisplayableInWorkspace(true);

        foreach ($roles as $role) {
            $widget->addRole($role);
        }
        $this->om->persist($widget);
        $this->om->flush();
    }
}
