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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HepnRemoveUserCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    private $params = [
        'path' => 'Absolute path to the file: ',
    ];

    protected function configure()
    {
        $this->setName('claroline:hepn:clear')
            ->setDescription('Create a workspace from a zip archive (for debug purpose)');
        $this->setDefinition(
            [
                new InputArgument('path', InputArgument::REQUIRED, 'The absolute path to the file.'),
            ]
        );

        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'When set to true, doesn\'t ask for a confirmation'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('path');
        $lines = str_getcsv(file_get_contents($file), PHP_EOL);

        $emails = array_map(function ($line) {
            return str_getcsv($line, ';')[3];
        }, $lines);

        $om = $this->getContainer()->get(ObjectManager::class);
        $users = $om->getRepository(User::class)->findAll();
        $i = 0;
        $total = 0;
        $force = $input->getOption('force');

        if (!$force) {
            $output->writeln('Dry run');
        }

        foreach ($users as $user) {
            if (!in_array($user->getEmail(), $emails) && strpos($user->getEmail(), '@students.hepn.be')) {
                $output->writeln('Ready to remove user '.$user->getEmail());
                ++$i;
                ++$total;

                if ($force) {
                    $this->getContainer()->get('Claroline\AppBundle\API\Crud')->delete($user);
                }
            }
        }

        $output->writeln('Total: '.$total);
    }
}
