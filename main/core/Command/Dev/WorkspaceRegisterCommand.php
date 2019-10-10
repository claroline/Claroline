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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkspaceRegisterCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;
    private $params = ['user' => 'The username'];

    protected function configure()
    {
        $this->setName('claroline:workspace:register-to-archived');
        $this->setDefinition(
            [
                new InputArgument(
                    'user',
                    InputArgument::REQUIRED,
                    'The user username.'
                ),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $om = $this->getContainer()->get('Claroline\AppBundle\Persistence\ObjectManager');
        $workspaces = $om->getRepository(Workspace::class)->findBy(['archived' => true, 'personal' => false]);
        $user = $input->getArgument('user');
        $user = $om->getRepository(User::class)->findOneByUsername($user);
        $count = count($workspaces);
        $output->writeln($count.' found.');

        $i = 0;
        foreach ($workspaces as $workspace) {
            ++$i;
            $roles = $workspace->getRoles();
            foreach ($roles as $role) {
                if ('collaborator' === $role->getTranslationKey()) {
                    $output->writeln($i.'/'.$count.' collaborator added');

                    $user->addRole($role);
                    $om->persist($user);
                }
            }
        }

        $om->flush();
    }
}
