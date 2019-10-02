<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120506 extends Updater
{
    protected $logger;
    private $container;
    /** @var ObjectManager */
    private $om;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function preUpdate()
    {
        $this->renameTool('open-badge', 'badges');
    }

    private function renameTool($oldName, $newName)
    {
        $this->log(sprintf('Renaming `%s` tool into `%s`...', $oldName, $newName));

        $tool = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneBy(['name' => $oldName]);
        if (!empty($tool)) {
            $tool->setName($newName);
            $this->om->persist($tool);
            $this->om->flush();
        }
    }
}
