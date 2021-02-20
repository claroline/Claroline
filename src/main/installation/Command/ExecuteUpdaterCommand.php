<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Command;

use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use Claroline\InstallationBundle\Repository\UpdaterExecutionRepository;
use Claroline\InstallationBundle\Updater\NonReplayableUpdaterInterface;
use Claroline\InstallationBundle\Updater\Updater;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Executes a single plugin updater.
 */
class ExecuteUpdaterCommand extends Command
{
    private $updaterExecutionRepository;
    private $updaters;

    /**
     * @param ContainerInterface|Updater[] $updaters The Updater service registry
     */
    public function __construct(UpdaterExecutionRepository $updaterExecutionRepository, ContainerInterface $updaters)
    {
        $this->updaterExecutionRepository = $updaterExecutionRepository;
        $this->updaters = $updaters;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Executes a single plugin updater.')
            ->addArgument('updater_class', InputArgument::REQUIRED, 'The fully qualified classname of the updater that should be executed.')
            ->addOption('install', 'i', InputOption::VALUE_NONE, 'If passed, pre/post install operations will be executed.')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'When set to true, the Updater will be executed regardless if it has been already.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $updaterClass = $input->getArgument('updater_class');

        if (!$this->updaters->has($updaterClass)) {
            throw new InvalidArgumentException(sprintf('Updater "%s" does not exist.', $updaterClass));
        }

        if ($this->updaterExecutionRepository->hasBeenExecuted($updaterClass) && (!$input->getOption('force') || \is_subclass_of($updaterClass, NonReplayableUpdaterInterface::class))) {
            throw new RuntimeException(sprintf('Updater "%s" has been already executed.', $updaterClass));
        }

        MaintenanceHandler::enableMaintenance();

        /** @var Updater $updater */
        $updater = $this->updaters->get($updaterClass);

        try {
            if ($input->getOption('install')) {
                $this->executeInstallOperations($updater, $output);
            } else {
                $this->executeUpdateOperations($updater, $output);
            }
        } finally {
            MaintenanceHandler::disableMaintenance();
        }

        $output->writeln(
            sprintf('<comment>%s - Updater execution terminated.</comment>', date('H:i:s'))
        );

        return 0;
    }

    private function executeInstallOperations(Updater $updater, OutputInterface $output): void
    {
        if (method_exists($updater, 'preInstall')) {
            $output->writeln(
                sprintf('<comment>%s - Executing pre-install operations from "%s"...</comment>', date('H:i:s'), \get_class($updater))
            );

            $updater->preInstall();
        }

        if (method_exists($updater, 'postInstall')) {
            $output->writeln(
                sprintf('<comment>%s - Executing post-install operations from "%s"...</comment>', date('H:i:s'), \get_class($updater))
            );

            $updater->postInstall();
        }
    }

    private function executeUpdateOperations(Updater $updater, OutputInterface $output): void
    {
        if (method_exists($updater, 'preUpdate')) {
            $output->writeln(
                sprintf('<comment>%s - Executing pre-update operations from "%s"...</comment>', date('H:i:s'), \get_class($updater))
            );

            $updater->preUpdate();
        }

        if (method_exists($updater, 'postUpdate')) {
            $output->writeln(
                sprintf('<comment>%s - Executing post-update operations from "%s"...</comment>', date('H:i:s'), \get_class($updater))
            );

            $updater->postUpdate();
        }
    }
}
