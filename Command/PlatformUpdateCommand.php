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
use Symfony\Component\Console\Input\InputInterface;
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
        $output->writeln('<comment>Updating the platform...</comment>');
        $installer = $this->getContainer()->get('claroline.installation.platform_installer');
        $refresher = $this->getContainer()->get('claroline.installation.refresher');
        $installer->setOutput($output);
        $installer->setLogger(
            function ($message) use ($output) {
                $output->writeln($message);
            }
        );
        $installer->installFromOperationFile();
        $refresher->dumpAssets($this->getContainer()->getParameter('kernel.environment'));
        $refresher->compileGeneratedThemes();

        MaintenanceHandler::disableMaintenance();
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
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }
}
