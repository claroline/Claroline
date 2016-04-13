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

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater040200 extends Updater
{
    private $container;
    private $om;

    const MAX_BATCH_SIZE = 100;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateBaseRoles();
        $this->updateFacets();
        $this->setProfileProperties();
    }

    private function updateBaseRoles()
    {
        $this->log('Updating base roles...');
        $roles = $this->om->getRepository('ClarolineCoreBundle:Role')
            ->findAllPlatformRoles();

        foreach ($roles as $role) {
            $role->setPersonalWorkspaceCreationEnabled(true);
            $this->om->persist($role);
        }

        $this->om->flush();
    }

    private function updateFacets()
    {
        $this->log('Updating facets...');
        $facets = $this->om->getRepository('ClarolineCoreBundle:Facet\Facet')->findAll();
        $this->om->startFlushSuite();

        foreach ($facets as $facet) {
            $panel = $this->om->getRepository('ClarolineCoreBundle:Facet\PanelFacet')
                ->findByName($facet->getName());
            if (!$panel) {
                $this->container->get('claroline.manager.facet_manager')
                    ->addPanel($facet, $facet->getName());
            }
        }

        $this->om->endFlushSuite();
    }

    private function setProfileProperties()
    {
        $this->log('Updating profile properties...');
        $manager = $this->container->get('claroline.manager.profile_property_manager');
        $manager->addDefaultProperties();
    }
}
