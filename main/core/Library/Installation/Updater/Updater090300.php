<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/1/17
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater090300 extends Updater
{
    private $container;
    private $workspaceManager;
    private $orgaManager;
    protected $logger;
    private $fileSystem;
    private $iconSetsDir;

    public function __construct(ContainerInterface $container, $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->workspaceManager = $this->container->get('claroline.manager.workspace_manager');
        $this->workspaceManager->setLogger($logger);
        $this->orgaManager = $this->container->get('claroline.manager.organization.organization_manager');
        $this->orgaManager->setLogger($logger);
        $this->fileSystem = $container->get('filesystem');
        $this->iconSetsDir = $container->getParameter('claroline.param.icon_sets_directory');
    }

    public function postUpdate()
    {
        $this->workspaceManager->bindWorkspaceToOrganization();
        $this->createPublicDirectory();
    }

    private function createPublicDirectory()
    {
        if (!$this->fileSystem->exists($this->iconSetsDir)) {
            $this->log('Creating icon sets directory in public files directory...');
            $this->fileSystem->mkdir($this->iconSetsDir, 0775);
            $this->fileSystem->chmod($this->iconSetsDir, 0775, 0000, true);
        }
    }
}
