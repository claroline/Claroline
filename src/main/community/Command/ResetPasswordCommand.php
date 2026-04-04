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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetPasswordCommand extends Command
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly Crud $crud
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Resets the password of a user.');
        $this->setDefinition([
            new InputArgument('identifier', InputArgument::REQUIRED, 'The username or email of the user'),
            new InputArgument('password', InputArgument::REQUIRED, 'The new password'),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $identifier = $input->getArgument('identifier');
        $user = $this->om->getRepository(User::class)->findOneBy(['username' => $identifier])
            ?? $this->om->getRepository(User::class)->findOneBy(['email' => $identifier]);

        if (!$user) {
            $output->writeln(sprintf('<error>User "%s" not found.</error>', $identifier));

            return Command::FAILURE;
        }

        $this->crud->update($user, [
            'plainPassword' => $input->getArgument('password'),
        ], [Crud::NO_PERMISSIONS]);

        $output->writeln(sprintf('<info>Password updated for user "%s".</info>', $identifier));

        return Command::SUCCESS;
    }
}
