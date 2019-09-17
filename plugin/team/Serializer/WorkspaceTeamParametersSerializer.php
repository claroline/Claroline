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
     * TeamParamtersSerializer constructor.
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->workspaceRepo = $om->getRepository(Workspace::class);
    }

    /**
     * Serializes a WorkspaceTeamParameters entity for the JSON api.
     *
     * @param WorkspaceTeamParameters $parameters
     *
     * @return array - the serialized representation of the WorkspaceTeamParameters entity
     */
    public function serialize(WorkspaceTeamParameters $parameters)
    {
        $serialized = [
            'id' => $parameters->getUuid(),
            'selfRegistration' => $parameters->isSelfRegistration(),
            'selfUnregistration' => $parameters->isSelfUnregistration(),
            'publicDirectory' => $parameters->isPublic(),
            'deletableDirectory' => $parameters->isDirDeletable(),
            'allowedTeams' => $parameters->getMaxTeams(),
            'workspace' => [
              'uuid' => $parameters->getWorkspace()->getUuid(),
            ],
        ];

        return $serialized;
    }

    /**
     * @param array                   $data
     * @param WorkspaceTeamParameters $parameters
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

        if (isset($data['workspace']['uuid'])) {
            $workspace = $this->workspaceRepo->findOneBy(['uuid' => $data['workspace']['uuid']]);

            if ($workspace) {
                $parameters->setWorkspace($workspace);
            }
        }

        return $parameters;
    }
}
