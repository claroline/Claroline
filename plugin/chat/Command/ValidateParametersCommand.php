<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Command;

use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Performs a fresh installation of the platform.
 */
class ValidateParametersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:chat:validate');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = ConsoleLogger::get($output);
        $chatManager = $this->getContainer()->get('claroline.manager.chat_manager');
        $configHandler = $this->getContainer()->get('claroline.config.platform_config_handler');
        $chatManager->setLogger($consoleLogger);
        $host = $configHandler->getParameter('chat_xmpp_host');
        $user = $configHandler->getParameter('chat_admin_username');
        $pw = $configHandler->getParameter('chat_admin_password');
        $muc = $configHandler->getParameter('chat_xmpp_muc_host');
        $bosh = $configHandler->getParameter('chat_bosh_port');
        $ice = $configHandler->getParameter('chat_ice_servers');
        $ssl = $configHandler->getParameter('chat_ssl');
        $chatManager->validateParameters($host, $muc, $bosh, $ice, $user, $pw, $ssl);
    }
}
