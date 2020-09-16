<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Performs a fresh installation of the platform.
 */
class PlatformInstallCommand extends Command
{
    private $filesDir;

    public function __construct(string $filesDir)
    {
        parent::__construct();

        $this->filesDir = $filesDir;
    }

    protected function configure()
    {
        $this->setDescription('Installs the platform.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this
            ->getApplication()
            ->get('claroline:update')
            ->run(new ArrayInput([]), $output);

        return 0;
    }
}
