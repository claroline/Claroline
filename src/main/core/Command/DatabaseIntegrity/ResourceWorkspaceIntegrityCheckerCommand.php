<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceWorkspaceIntegrityCheckerCommand extends Command
{
    private $resourceManager;

    public function __construct(ResourceManager $resourceManager)
    {
        $this->resourceManager = $resourceManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Checks the resource integrity of the platform.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->resourceManager->checkIntegrity();

        return 0;
    }
}
