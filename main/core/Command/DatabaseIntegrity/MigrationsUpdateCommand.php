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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationsUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:migrations:mark_migrated')
            ->setDescription('This command allows you to set the migrations to their latest version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get('claroline.manager.plugin_manager');
        $bundles = $manager->getInstalledBundles();
        $command = $this->getApplication()->find('claroline:migration:version');

        foreach ($bundles as $bundle) {
            try {
                $arguments = [
                  'command' => 'claroline:migration:version',
                  'bundle' => $bundle['instance']->getShortName(),
                  '--all' => true,
              ];
                $command->run(new ArrayInput($arguments), $output);
            } catch (\Exception $e) {
                $output->writeln("<error>Error while upgrade {$bundle['instance']->getShortName()}</error>");
            }
        }
    }
}
