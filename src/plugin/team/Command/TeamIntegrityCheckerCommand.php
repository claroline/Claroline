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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TeamBundle\Manager\TeamManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TeamIntegrityCheckerCommand extends Command
{
    private $teamManager;
    private $om;

    public function __construct(TeamManager $teamManager, ObjectManager $om)
    {
        $this->teamManager = $teamManager;
        $this->om = $om;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDefinition([new InputArgument('code', InputArgument::OPTIONAL, 'The workspace code')]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $code = $input->getArgument('code');
        $workspaces = $code ?
          [$this->om->getRepository(Workspace::class)->findOneByCode($code)] :
          $this->om->getRepository(Workspace::class)->findBy(['personal' => false]);

        foreach ($workspaces as $workspace) {
            $teams = $this->teamManager->getTeamsByWorkspace($workspace);
            $output->writeln('Found '.count($teams).' team(s) for workspace '.$workspace->getCode());
            foreach ($teams as $team) {
                $output->writeln('Initialize perms for team '.$team->getName());
                $roles = [
                  $team->getRole(),
                  $team->getTeamManagerRole(),
                ];
                $this->teamManager->initializeTeamPerms($team, $roles);
            }
        }

        return 0;
    }
}
