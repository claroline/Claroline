<?php

namespace Claroline\TeamBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\TeamBundle\Entity\WorkspaceTeamParameters;

class WorkspaceTeamParametersSerializer
{
    use SerializerTrait;

    /** @var WorkspaceRepository */
    private $workspaceRepo;

    /**
     * WorkspaceTeamParametersSerializer constructor.
     */
    public function __construct(ObjectManager $om)
    {
        $this->workspaceRepo = $om->getRepository(Workspace::class);
    }

    public function getName()
    {
        return 'workspace_team_parameters';
    }

    /**
     * Serializes a WorkspaceTeamParameters entity for the JSON api.
     *
     * @return array - the serialized representation of the WorkspaceTeamParameters entity
     */
    public function serialize(WorkspaceTeamParameters $parameters)
    {
        return [
            'id' => $parameters->getUuid(),
            'selfRegistration' => $parameters->isSelfRegistration(),
            'selfUnregistration' => $parameters->isSelfUnregistration(),
            'publicDirectory' => $parameters->isPublic(),
            'deletableDirectory' => $parameters->isDirDeletable(),
            'allowedTeams' => $parameters->getMaxTeams(),
            'workspace' => [ // TODO : use workspaceSerializer instead
                'id' => $parameters->getWorkspace()->getUuid(),
            ],
        ];
    }

    /**
     * @param array $data
     *
     * @return WorkspaceTeamParameters
     */
    public function deserialize($data, WorkspaceTeamParameters $parameters, array $options = [])
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $parameters->setUuid($data['id']);
        }

        $this->sipe('selfRegistration', 'setSelfRegistration', $data, $parameters);
        $this->sipe('selfUnregistration', 'setSelfUnregistration', $data, $parameters);
        $this->sipe('publicDirectory', 'setIsPublic', $data, $parameters);
        $this->sipe('deletableDirectory', 'setDirDeletable', $data, $parameters);
        $this->sipe('allowedTeams', 'setMaxTeams', $data, $parameters);

        if (isset($data['workspace']['id'])) {
            /** @var Workspace $workspace */
            $workspace = $this->workspaceRepo->findOneBy(['uuid' => $data['workspace']['id']]);

            if ($workspace) {
                $parameters->setWorkspace($workspace);
            }
        }

        return $parameters;
    }
}
