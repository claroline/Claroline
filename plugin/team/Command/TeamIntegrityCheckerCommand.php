<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TeamIntegrityCheckerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:teams:check');
        $this->setDefinition([new InputArgument('code', InputArgument::OPTIONAL, 'The workspace code')]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        $teamManager = $this->getContainer()->get('claroline.manager.team_manager');
        $om = $this->getContainer()->get('claroline.persistence.object_manager');

        $workspaces = $code ?
          [$om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findOneByCode($code)] :
          $om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findBy(['personal' => false]);

        foreach ($workspaces as $workspace) {
            $teams = $teamManager->getTeamsByWorkspace($workspace);
            $output->writeln('Found '.count($teams).' team(s) for workspace '.$workspace->getCode());
            foreach ($teams as $team) {
                $output->writeln('Initialize perms for team '.$team->getName());
                $roles = [
                  $team->getRole(),
                  $team->getTeamManagerRole(),
                ];
                $teamManager->initializeTeamPerms($team, $roles);
            }
        }
    }
}
