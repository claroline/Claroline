<?php

namespace Claroline\AppBundle\Manager;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpKernel\KernelInterface;

class CommandManager
{
    public function __construct(
        private readonly KernelInterface $kernel
    ) {
    }

    public function run(ArrayInput $input, $output): void
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $application->run($input, $output);
    }
}
