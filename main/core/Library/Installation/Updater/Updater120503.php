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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120503 extends Updater
{
    /** @var ContainerInterface */
    private $container;
    private $conn;
    /** @var ObjectManager */
    private $om;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->removeOldTools();
    }

    private function removeOldTools()
    {
        $toolManager = $this->container->get(ToolManager::class);
        $toolManager->setLogger($this->logger);
        $toolManager->cleanOldTools();
    }
}
