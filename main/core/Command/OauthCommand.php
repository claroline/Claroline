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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Claroline\CoreBundle\Entity\Oauth\FriendRequest;

class OauthCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('claroline:friend:request')
            ->setDescription('Send a friend request to a Claroline platform');
        $this->setDefinition(
            array(
                new InputArgument('master', InputArgument::REQUIRED, 'The platform master'),
                new InputArgument('host', InputArgument::REQUIRED, 'The platform host'),
                new InputArgument('name', InputArgument::REQUIRED, 'The platform name'),
            )
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'master' => 'master',
            'host' => 'host',
            'name' => 'name',
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
            "Enter the platform {$argumentName}: ",
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
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $host = $input->getArgument('host');
        $master = $input->getArgument('master');
        $name = $input->getArgument('name');
        $request = new FriendRequest();
        $request->setHost($host);
        $request->setName($name);
        $om->persist($request);
        $om->flush();
        $this->getContainer()->get('claroline.manager.oauth_manager')->createFriendRequest($request, $master);
        $output->writeln('Platform added !');
    }
}
