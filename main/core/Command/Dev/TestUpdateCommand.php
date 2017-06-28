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

use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class TestUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:test_update')
            ->setAliases(['claroline:debug:update'])
            ->setDescription('Tests the local update of a bundle.');
        $this->setDefinition(
            [
                new InputArgument('bundle', InputArgument::REQUIRED, 'bundle'),
                new InputArgument('from_version', InputArgument::REQUIRED, 'from version'),
                new InputArgument('to_version', InputArgument::REQUIRED, 'to version'),
            ]
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = [
            'from_version' => 'from version: ',
            'to_version' => 'to version: ',
            'bundle' => 'bundle: ',
        ];

        foreach ($params as $argument => $argumentName) {
            if (!$input->getArgument($argument)) {
                $input->setArgument(
                    $argument, $this->askArgument($output, $argumentName)
                );
            }
        }
    }

    protected function askArgument(OutputInterface $output, $argumentName)
    {
        $argument = $this->getHelper('dialog')->askAndValidate(
            $output,
            $argumentName,
            function ($argument) {
                if ($argument === null) {
                    throw new \Exception('This argument is required');
                }

                return $argument;
            }
        );

        return $argument;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $bundleName = $input->getArgument('bundle');
        $bundle = $container->get('kernel')->getBundle($bundleName);
        $installerType = $bundle instanceof DistributionPluginBundle ?
            'claroline.plugin.installer' :
            'claroline.installation.manager';

        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ];
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);

        //for historical reasons, CoreBundle might not be installed yet...
        if ($bundleName === 'ClarolineCoreBundle') {
            $om = $container->get('claroline.persistence.object_manager');
            $plugin = $om->getRepository('ClarolineCoreBundle:Plugin')->findOneBy([
              'vendorName' => 'Claroline', 'bundleName' => 'CoreBundle',
            ]);
            if (!$plugin) {
                $plugin = new Plugin();
                $plugin->setBundleName('CoreBundle');
                $plugin->setVendorName('Claroline');
                $om->persist($plugin);
                $om->flush();
            }
        }

        /** @var \Claroline\InstallationBundle\Manager\InstallationManager|\Claroline\CoreBundle\Library\Installation\Plugin\Installer $installer */
        $installer = $this->getContainer()->get($installerType);
        $installer->setLogger($consoleLogger);
        $from = $input->getArgument('from_version');
        $to = $input->getArgument('to_version');
        $installer->update($bundle, $from, $to);
        $container->get('claroline.installation.operation_executor')->end();
    }
}
