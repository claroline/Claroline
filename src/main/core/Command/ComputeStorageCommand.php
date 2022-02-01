<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command;

use Claroline\CoreBundle\Manager\FileManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComputeStorageCommand extends Command
{
    private $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Compute used storage (content of files directory) and store result in platform options');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Computing used storage...');

        $usedStorage = $this->fileManager->computeUsedStorage();
        $this->fileManager->updateUsedStorage($usedStorage);

        $output->writeln('Used storage : '.$usedStorage.'B');

        if ($this->fileManager->isStorageFull()) {
            $output->writeln('<comment>ATTENTION : Platform storage is full. File upload is disabled.</comment>');
        }

        return 0;
    }
}
