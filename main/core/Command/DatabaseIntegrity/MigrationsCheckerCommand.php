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

use Claroline\CoreBundle\Command\Traits\BaseCommandTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationsCheckerCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    private $params = [
        'bundle' => 'The bundle name: ',
        'version' => 'The migration version: ',
    ];

    protected function configure()
    {
        $this->setName('claroline:migrations:check')
            ->setDescription('This command allows you to add populate the doctrine migration table. It might be improved later');
        $this->configureParams();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getArgument('version');
        $bundle = $input->getArgument('bundle');
        $manager = $this->getContainer()->get('claroline.manager.migration_manager');

        if (!$manager->exists($bundle, $version)) {
            $output->writeln('Marking migration...');
            $manager->mark($bundle, $version);
        } else {
            $output->writeln('Migration already exists.');
        }
    }
}
