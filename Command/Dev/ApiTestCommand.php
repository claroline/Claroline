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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Claroline\CoreBundle\Library\Workspace\Configuration;

class ApiTestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:api:test')->setDescription('Tests the api');
        $this->setDefinition(
            array(
                new InputArgument('host', InputArgument::REQUIRED, 'The host'),
                new InputArgument('url', InputArgument::REQUIRED, 'The url'),
                new InputArgument('client_name', InputArgument::REQUIRED, 'The client name')
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'host' => 'The host',
            'url' => 'The url',
            'client_name' => 'The client name'
        );

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
            "Enter the {$argumentName}: ",
            function ($argument) {
                if (empty($argument)) {
                    throw new \Exception('This argument is required');
                }

                return $argument;
            }
        );

        return $argument;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $apiManager = $this->getContainer()->get('claroline.manager.api_manager');
        $host = $input->getArgument('host');
        $url = $input->getArgument('url');
        $clientName = $input->getArgument('client_name');
        $client = $this->getContainer()->get('claroline.manager.oauth_manager')->findClientBy(array('name' => $clientName));
        $response = $apiManager->url($host, $url, $client);
        echo $response;
    }
}
