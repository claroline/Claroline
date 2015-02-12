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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Tool\PwsToolConfig;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\Resource\PwsRightsManagementAccess;

class Updater040200
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
        $facets = $this->om->getRepository('ClarolineCoreBundle:Facet\Facet')->findAll();
        $this->om->startFlushSuite();

        foreach ($facets as $facet) {
            $this->container->get('claroline.manager.facet_manager')
                ->addPanel($facet, $facet->getName());
        }

        $this->om->endFlushSuite();
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
