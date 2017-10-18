<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 17/10/17
 * Time: 14:10.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IconSetCalibrateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:icon_set:calibrate')
            ->setDescription('This command allow you to recalibrate icons in the icon set');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = ConsoleLogger::get($output);
        $this->getContainer()->get('claroline.manager.icon_set_manager')->setLogger($logger);
        $this->getContainer()->get('claroline.manager.icon_set_manager')->calibrateIconSets();
    }
}
