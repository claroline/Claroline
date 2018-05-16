<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 7/7/17
 * Time: 9:33 AM.
 */

namespace Claroline\CoreBundle\Command;

use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeIconSetCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    private $params = [
        'icon_set_slug' => 'icon set slug',
    ];

    protected function configure()
    {
        $this->setName('claroline:icon_set:change')
            ->setDescription('Change platform icon set');

        $this->setDefinition(
            [
                new InputArgument('icon_set_slug', InputArgument::REQUIRED, 'The icon set slug'),
            ]
        );

        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force even if current set is the active set'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $iconSetSlug = $input->getArgument('icon_set_slug');

        $force = false;
        if ($input->getOption('force')) {
            $force = true;
        }

        $iconSetManager = $this->getContainer()->get('claroline.manager.icon_set_manager');
        $consoleLogger = ConsoleLogger::get($output);
        $iconSetManager->setLogger($consoleLogger);
        $iconSetManager->setActiveResourceIconSetByCname($iconSetSlug, $force);
        $configHandler = $this->getContainer()->get('claroline.config.platform_config_handler');
        $configHandler->setParameter('resource_icon_set', $iconSetSlug);
    }
}
