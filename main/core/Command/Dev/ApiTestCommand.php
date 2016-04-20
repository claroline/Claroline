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

class ApiTestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:api:test')->setDescription('Tests the api');
        $this->setDefinition(
            array(
                new InputArgument('platform_name', InputArgument::REQUIRED, 'the friend request name'),
                new InputArgument('url', InputArgument::REQUIRED, 'the url'),
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'platform_name' => 'the friend request name',
            'url' => 'the url',
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
        $name = $input->getArgument('platform_name');
        $url = $input->getArgument('url');
        $friend = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Oauth\FriendRequest')->findOneByName($name);
        $response = $apiManager->url($friend, $url);
        echo $response;
    }
}
