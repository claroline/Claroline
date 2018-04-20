<?php

namespace Claroline\CoreBundle\API\Serializer\Workspace;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.serializer.workspace")
 * @DI\Tag("claroline.serializer")
 */
class WorkspaceSerializer
{
    use SerializerTrait;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /** @var ContainerInterface */
    private $container;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * WorkspaceSerializer constructor.
     *
     * @DI\InjectParams({
     *     "userSerializer"   = @DI\Inject("claroline.serializer.user"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "container"        = @DI\Inject("service_container"),
     *     "serializer"       = @DI\Inject("claroline.api.serializer"),
     *     "ut"               = @DI\Inject("claroline.utilities.misc"),
     *     "fileUt"           = @DI\Inject("claroline.utilities.file")
     * })
     *
     * @param UserSerializer   $userSerializer
     * @param WorkspaceManager $workspaceManager
     */
    public function __construct(
        UserSerializer $userSerializer,
        WorkspaceManager $workspaceManager,
        ContainerInterface $container,
        SerializerProvider $serializer,
        ClaroUtilities $ut,
        FileUtilities $fileUt
    ) {
        $this->userSerializer = $userSerializer;
        $this->workspaceManager = $workspaceManager;
        $this->container = $container;
        $this->serializer = $serializer;
        $this->ut = $ut;
        $this->fileUt = $fileUt;
    }

    /**
     * Serializes a Workspace entity for the JSON api.
     *
     * @param Workspace $workspace - the workspace to serialize
     * @param array     $options   - a list of serialization options
     *
     * @return array - the serialized representation of the workspace
     */
    public function serialize(Workspace $workspace, array $options = [])
    {
        $serialized = [
            'id' => $workspace->getId(),
            'uuid' => $workspace->getGuid(), // todo: should be merged with `id`
            'name' => $workspace->getName(),
            'code' => $workspace->getCode(),
            'thumbnail' => null, // todo : add as Workspace prop
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serializer = $this->serializer;
            $serialized = array_merge($serialized, [
                'thumbnail' => $workspace->getThumbnail() ? $this->container->get('claroline.serializer.public_file')->serialize($workspace->getThumbnail()) : null,
                'meta' => $this->getMeta($workspace),
                'display' => $this->getDisplay($workspace),
                'restrictions' => $this->getRestrictions($workspace),
                'registration' => $this->getRegistration($workspace),
                'roles' => array_map(function (Role $role) {
                    return [
                        'id' => $role->getUuid(),
                        'name' => $role->getName(),
                        'translationKey' => $role->getTranslationKey(),
                    ];
                }, $workspace->getRoles()->toArray()),
                'managers' => array_map(function (User $manager) {
                    return $this->userSerializer->serialize($manager, [Options::SERIALIZE_MINIMAL]);
                }, $this->workspaceManager->getManagers($workspace)),
                'organizations' => array_map(function ($organization) use ($serializer) {
                    return $serializer->serialize($organization);
                }, $workspace->getOrganizations()->toArray()),
                'options' => $this->getOptions($workspace),
            ]);
        }

        //maybe do the same for users one day
        if (in_array(Options::WORKSPACE_FETCH_GROUPS, $options)) {
            $serialized['groups'] = $this->container->get('claroline.api.finder')->search(
              'Claroline\CoreBundle\Entity\Group',
              ['filters' => ['workspace' => $workspace->getUuid()]],
              [Options::SERIALIZE_MINIMAL]
            )['data'];
        }

        return $serialized;
    }

    /**
     * @param Workspace $workspace
     *
     * @return array
     */
    private function getMeta(Workspace $workspace)
    {
        return [
            'slug' => $workspace->getSlug(),
            'model' => $workspace->isModel(),
            'personal' => $workspace->isPersonal(),
            'description' => $workspace->getDescription(),
            'created' => $workspace->getCreated()->format('Y-m-d\TH:i:s'),
            'creator' => $workspace->getCreator() ? $this->userSerializer->serialize($workspace->getCreator(), [Options::SERIALIZE_MINIMAL]) : null,
            'usedStorage' => $this->ut->formatFileSize($this->workspaceManager->getUsedStorage($workspace)),
            'totalUsers' => $this->workspaceManager->countUsers($workspace, true),
            'totalResources' => $this->workspaceManager->countResources($workspace),
            'notifications' => !$workspace->isDisabledNotifications(),
        ];
    }

    /**
     * @param Workspace $workspace
     *
     * @return array
     */
    private function getDisplay(Workspace $workspace)
    {
        return [
            'displayable' => $workspace->isDisplayable(), // deprecated
        ];
    }

    private function getOptions(Workspace $workspace)
    {
        $options = $this->workspaceManager->getWorkspaceOptions($workspace)->getDetails();

        if (isset($options['workspace_opening_resource']) && $options['workspace_opening_resource']) {
            $resource = $this->serializer->deserialize(
              'Claroline\CoreBundle\Entity\Resource\ResourceNode',
               ['id' => $options['workspace_opening_resource']]
            );

            if ($resource->getName()) {
                $options['opened_resource'] = $this->serializer->serialize($resource);
            } else {
                $options['opened_resource'] = null;
            }
        }

        return $options;
    }

    /**
     * @param Workspace $workspace
     *
     * @return array
     */
    private function getRestrictions(Workspace $workspace)
    {
        return [
            'hidden' => $workspace->isHidden(),
            'accessibleFrom' => $workspace->getStartDate() ? $workspace->getStartDate()->format('Y-m-d\TH:i:s') : null,
            'accessibleUntil' => $workspace->getEndDate() ? $workspace->getEndDate()->format('Y-m-d\TH:i:s') : null,
            'maxUsers' => $workspace->getMaxUsers(),
            'maxStorage' => $workspace->getMaxStorageSize(),
            'maxResources' => $workspace->getMaxUploadResources(),
        ];
    }

    /**
     * @param Workspace $workspace
     *
     * @return array
     */
    private function getRegistration(Workspace $workspace)
    {
        return [
            'validation' => $workspace->getRegistrationValidation(),
            'selfRegistration' => $workspace->getSelfRegistration(),
            'selfUnregistration' => $workspace->getSelfUnregistration(),
            'defaultRole' => $workspace->getDefaultRole() ?
              $this->serializer->serialize($workspace->getDefaultRole(), [Options::SERIALIZE_MINIMAL]) :
              null,
        ];
    }

    /**
     * Deserializes Workspace data into entities.
     *
     * @param array     $data
     * @param Workspace $workspace
     * @param array     $options
     *
     * @return Workspace
     */
    public function deserialize(array $data, Workspace $workspace, array $options = [])
    {
        // remove this later (with the Trait)
        //$this->genericSerializer->deserialize($data, $workspace, $options);

        if (isset($data['thumbnail']) && isset($data['thumbnail']['id'])) {
            $thumbnail = $this->serializer->deserialize(
                'Claroline\CoreBundle\Entity\File\PublicFile',
                $data['thumbnail']
            );
            $workspace->setThumbnail($thumbnail);
            $this->fileUt->createFileUse(
                $thumbnail,
                'Claroline\CoreBundle\Entity\Workspace',
                $workspace->getUuid()
            );
        }

        if (isset($data['registration']) && isset($data['registration']['defaultRole'])) {
            $defaultRole = $this->serializer->deserialize(
                'Claroline\CoreBundle\Entity\Role',
                $data['registration']['defaultRole']
            );
            $workspace->setDefaultRole($defaultRole);
        }

        $this->sipe('uuid', 'setUuid', $data, $workspace);
        $this->sipe('code', 'setCode', $data, $workspace);
        $this->sipe('name', 'setName', $data, $workspace);
        $this->sipe('notifications', 'setNotifications', $data, $workspace);

        $this->sipe('meta.model', 'setIsModel', $data, $workspace);
        $this->sipe('meta.description', 'setDescription', $data, $workspace);

        $this->sipe('restrictions.hidden', 'setHidden', $data, $workspace);
        $this->sipe('restrictions.maxUsers', 'setMaxUsers', $data, $workspace);
        $this->sipe('restrictions.maxStorage', 'setMaxStorageSize', $data, $workspace);
        $this->sipe('restrictions.maxResources', 'setMaxUploadResources', $data, $workspace);

        $this->sipe('registration.validation', 'setRegistrationValidation', $data, $workspace);
        $this->sipe('registration.selfRegistration', 'setSelfRegistration', $data, $workspace);
        $this->sipe('registration.selfUnregistration', 'setSelfUnregistration', $data, $workspace);

        if (isset($data['restrictions']) && isset($data['restrictions']['accessibleFrom'])) {
            $workspace->setStartDate(DateNormalizer::denormalize($data['restrictions']['accessibleFrom']));
        }

        if (isset($data['restrictions']) && isset($data['restrictions']['accessibleUntil'])) {
            $workspace->setEndDate(DateNormalizer::denormalize($data['restrictions']['accessibleUntil']));
        }

        if (isset($data['options'])) {
            $workspaceOptions = $this->workspaceManager->getWorkspaceOptions($workspace);
            $workspaceOptions->setDetails($data['options']);
        }

        return $workspace;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/workspace.json';
    }

    /**
     * @return string
     */
    public function getSamples()
    {
        return '#/main/core/workspace';
    }
}
