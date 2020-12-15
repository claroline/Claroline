<?php

namespace Claroline\AppBundle\Manager;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpKernel\KernelInterface;

class CommandManager
{
    private $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function run(ArrayInput $input, $output)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $application->run($input, $output);
    }
}
