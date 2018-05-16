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

use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\CoreBundle\Entity\Oauth\FriendRequest;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OauthCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    private $params = [
        'master' => 'master',
        'host' => 'host',
        'name' => 'name',
    ];

    protected function configure()
    {
        parent::configure();

        $this->setName('claroline:friend:request')
            ->setDescription('Send a friend request to a Claroline platform');
        $this->setDefinition(
            [
                new InputArgument('master', InputArgument::REQUIRED, 'The platform master'),
                new InputArgument('host', InputArgument::REQUIRED, 'The platform host'),
                new InputArgument('name', InputArgument::REQUIRED, 'The platform name'),
            ]
        );
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
