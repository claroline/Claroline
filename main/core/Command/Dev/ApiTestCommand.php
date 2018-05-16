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

use Claroline\AppBundle\Command\BaseCommandTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApiTestCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    private $params = [
        'platform_name' => 'the friend request name',
        'url' => 'the url',
    ];

    protected function configure()
    {
        $this->setName('claroline:api:test')->setDescription('Tests the api');
        $this->setDefinition(
            [
                new InputArgument('platform_name', InputArgument::REQUIRED, 'the friend request name'),
                new InputArgument('url', InputArgument::REQUIRED, 'the url'),
            ]
        );
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
