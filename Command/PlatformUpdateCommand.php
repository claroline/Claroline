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

use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Updates, installs or uninstalls the core and plugin bundles, following
 * the operation order logged in *app/config/operations.xml* during
 * composer execution.
 *
 * @Service("claroline.command.update_command")
 */
class PlatformUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:update')
            ->setDescription(
                'Updates, installs or uninstalls the platform packages '
                . 'brought by composer (requires an operation file).'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<comment>%s - Updating the platform...</comment>', date('H:i:s')));
        $verbosityLevelMap = array(
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO   => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG  => OutputInterface::VERBOSITY_NORMAL
        );
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);

        /** @var \Claroline\CoreBundle\Library\Installation\PlatformInstaller $installer */
        $installer = $this->getContainer()->get('claroline.installation.platform_installer');
        $installer->setOutput($output);
        $installer->setLogger($consoleLogger);
        $installer->installFromOperationFile();

        /** @var \Claroline\CoreBundle\Library\Installation\Refresher $refresher */
        $refresher = $this->getContainer()->get('claroline.installation.refresher');

        $refresher->dumpAssets($this->getContainer()->getParameter('kernel.environment'));

        MaintenanceHandler::disableMaintenance();

        $output->writeln(sprintf('<comment>%s - Platform updated.</comment>', date('H:i:s')));
    }

    /**
     * {@inheritdoc}
     *
     * @InjectParams({
     *     "container" = @Inject("service_container")
     * })
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
    }
}
