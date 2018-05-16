<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Command;

use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\ForumBundle\Tests\DataFixtures\LoadForumData;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class CreateForumCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    private $params = [
        'username' => 'username',
        'name' => 'name',
        'subjectsAmount' => 'subjectsAmount',
        'messagesAmount' => 'messagesAmount',
    ];

    protected function configure()
    {
        $this->setName('claroline:forum:create')
            ->setDescription('Creates a forum.');
        $this->setDefinition(
            [
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('name', InputArgument::REQUIRED, 'The forum name'),
                new InputArgument('subjectsAmount', InputArgument::REQUIRED, 'The number of subjects'),
                new InputArgument('messagesAmount', InputArgument::REQUIRED, 'The number of messages'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $subjectsAmount = $input->getArgument('subjectsAmount');
        $messagesAmount = $input->getArgument('messagesAmount');
        $username = $input->getArgument('username');
        $name = $input->getArgument('name');
        $fixture = new LoadForumData($name, $username, $messagesAmount, $subjectsAmount);
        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ];
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);
        $fixture->setLogger($consoleLogger);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $referenceRepo = new ReferenceRepository($em);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->getContainer());
        $fixture->load($em);
    }
}
