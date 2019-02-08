<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 17/10/17
 * Time: 14:10.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Command\AdminCliCommand;
use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserIntegrityCheckerCommand extends ContainerAwareCommand implements AdminCliCommand
{
    protected function configure()
    {
        $this->setName('claroline:user:check');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = ConsoleLogger::get($output);
        $userManager = $this->getContainer()->get('claroline.manager.user_manager');
        $userManager->setLogger($consoleLogger);
        $userManager->bindUserToOrganization();
    }
}
