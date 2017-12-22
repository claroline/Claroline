<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 8/31/17
 * Time: 4:37 PM.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceMaskDecoderIntegrityCheckerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:resource_mask_decoder:check')
            ->setDescription('Checks the resource mask decoders integrity of the platform.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = ConsoleLogger::get($output);
        $maskManager = $this->getContainer()->get('claroline.manager.mask_manager');
        $maskManager->setLogger($consoleLogger);
        $maskManager->checkIntegrity();
    }
}
