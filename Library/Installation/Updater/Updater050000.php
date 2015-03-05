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
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater050000
{
    private $container;
    private $toolManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->toolManager = $container->get('claroline.manager.tool_manager');
    }

    public function postUpdate()
    {
        $this->createMessageDesktopTool();
    }

    private function createMessageDesktopTool()
    {
        $this->log('Creating message tool...');
        $tool = $this->toolManager->getOneToolByName('message');

        if (is_null($tool)) {
            $tool = new Tool();
            $tool->setName('message');
            $tool->setClass('envelope');
            $tool->setDisplayableInWorkspace(false);
            $tool->setDisplayableInDesktop(true);
            $this->toolManager->create($tool);
        }
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
