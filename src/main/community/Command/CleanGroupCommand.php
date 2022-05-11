<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Command;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanGroupCommand extends Command
{
    /** @var ObjectManager */
    private $om;
    /** @var UserManager */
    private $manager;

    public function __construct(ObjectManager $om, UserManager $manager)
    {
        $this->om = $om;
        $this->manager = $manager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Disable users of a group if they are not in a csv file.')
            ->addArgument('csv_path', InputArgument::REQUIRED, 'The absolute path to the csv file containing the users to keep')
            ->addArgument('group', InputArgument::REQUIRED, 'The name of the group');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->om = $this->getContainer()->get(ObjectManager::class);
        $this->manager = $this->getContainer()->get('claroline.manager.user_manager');

        // get group
        $group = $this->om->getRepository(Group::class)->findOneBy(['name' => $input->getArgument('group')]);
        if (!$group) {
            throw new \Exception('Group cannot be found.');
        }

        // get emails from file
        $file = $input->getArgument('csv_path');
        $lines = str_getcsv(file_get_contents($file), PHP_EOL);
        $emails = array_map(function ($line) {
            $email = str_getcsv($line, ';')[0];
            if ($email) {
                return trim($email);
            }

            return '';
        }, $lines);

        /** @var User[] $users */
        $users = $this->om->getRepository(User::class)->findByGroup($group);

        $this->om->startFlushSuite();

        foreach ($users as $i => $user) {
            if (!in_array($user->getEmail(), $emails)) {
                $output->writeln(sprintf('Disable user %s.', $user->getEmail()));

                $this->manager->disable($user);

                if (0 === $i % 200) {
                    $this->om->forceFlush();
                }
            }
        }

        $this->om->flush();
        $this->om->endFlushSuite();

        return 0;
    }
}
