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
use Claroline\CoreBundle\Entity\Tool\AdminTool;

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
        if (!$this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findOneByName('organization_management')) {
            $this->createTool();
        }

        $this->container->get('claroline.manager.administration_manager')->addDefaultUserAdminActions();
    }

    private function createTool()
    {
        $this->log('Creating institution admin tool...');
        $entity = new AdminTool();
        $entity ->setName('organization_management');
        $entity ->setClass('institution');
        $this->om->persist($entity);
        $this->om->flush();
    }
}
