<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Manager\Transfer;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\Transfer\Tools\ToolImporterInterface;
use Claroline\TeamBundle\Entity\Team as TeamEntity;
use Claroline\TeamBundle\Entity\WorkspaceTeamParameters;
use Claroline\TeamBundle\Manager\TeamManager;
use Claroline\TeamBundle\Serializer\TeamSerializer;
use Claroline\TeamBundle\Serializer\WorkspaceTeamParametersSerializer;

class Team implements ToolImporterInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var TeamSerializer */
    private $teamSerializer;
    /** @var TeamManager */
    private $teamManager;
    /** @var WorkspaceTeamParametersSerializer */
    private $parametersSerializer;

    public function __construct(
        ObjectManager $om,
        TeamSerializer $teamSerializer,
        TeamManager $teamManager,
        WorkspaceTeamParametersSerializer $parametersSerializer
    ) {
        $this->om = $om;
        $this->teamSerializer = $teamSerializer;
        $this->teamManager = $teamManager;
        $this->parametersSerializer = $parametersSerializer;
    }

    public function serialize(Workspace $workspace, array $options): array
    {
        return [
          'parameters' => $this->parametersSerializer->serialize($this->teamManager->getWorkspaceTeamParameters($workspace)),
          'teams' => array_map(function (TeamEntity $team) {
              return $this->teamSerializer->serialize($team);
          }, $this->om->getRepository(TeamEntity::class)->findBy(['workspace' => $workspace])),
        ];
    }

    public function deserialize(array $data, Workspace $workspace, array $options, FileBag $bag)
    {
        foreach ($data['teams'] as $teamData) {
            $team = new TeamEntity();
            $this->teamSerializer->deserialize($teamData, $team, $options);
            $team->setWorkspace($workspace);
            $this->om->persist($team);
        }

        $parameters = new WorkspaceTeamParameters();
        $this->parametersSerializer->deserialize($data['parameters'], $parameters, $options);
        $parameters->setWorkspace($workspace);
        $this->om->persist($parameters);
        $this->om->flush();
    }

    public function prepareImport(array $orderedToolData, array $data): array
    {
        return $data;
    }
}
