<?php

namespace Claroline\CoreBundle\API\Serializer\Workspace;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.serializer.workspace")
 * @DI\Tag("claroline.serializer")
 */
class WorkspaceSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var ObjectManager */
    private $om;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ClaroUtilities */
    private $utilities;

    /** @var FileUtilities */
    private $fileUt;

    /**
     * WorkspaceSerializer constructor.
     *
     * @DI\InjectParams({
     *     "authorization"    = @DI\Inject("security.authorization_checker"),
     *     "om"               = @DI\Inject("claroline.persistence.object_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "serializer"       = @DI\Inject("claroline.api.serializer"),
     *     "utilities"        = @DI\Inject("claroline.utilities.misc"),
     *     "fileUt"           = @DI\Inject("claroline.utilities.file")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ObjectManager                 $om
     * @param WorkspaceManager              $workspaceManager
     * @param SerializerProvider            $serializer
     * @param ClaroUtilities                $utilities
     * @param FileUtilities                 $fileUt
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        WorkspaceManager $workspaceManager,
        SerializerProvider $serializer,
        ClaroUtilities $utilities,
        FileUtilities $fileUt)
    {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->workspaceManager = $workspaceManager;
        $this->serializer = $serializer;
        $this->utilities = $utilities;
        $this->fileUt = $fileUt;
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
            'uuid' => $workspace->getUuid(), // todo: should be merged with `id`
            'name' => $workspace->getName(),
            'code' => $workspace->getCode(),
            'thumbnail' => $workspace->getThumbnail() ? $this->serializer->serialize($workspace->getThumbnail()) : null,
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'permissions' => [ // TODO it should be available in list mode too, but will decrease perfs, should be tested
                    'open' => $this->authorization->isGranted('OPEN', $workspace),
                    'delete' => $this->authorization->isGranted('DELETE', $workspace),
                    'administrate' => $this->authorization->isGranted('ADMINISTRATE', $workspace),
                    'export' => $this->authorization->isGranted('EXPORT', $workspace)
                ],
                'meta' => $this->getMeta($workspace, $options),
                'opening' => $this->getOpening($workspace),
                'display' => $this->getDisplay($workspace),
                'restrictions' => $this->getRestrictions($workspace),
                'registration' => $this->getRegistration($workspace),
                'notifications' => $this->getNotifications($workspace),
                'roles' => array_map(function (Role $role) {
                    return $this->serializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
                }, $this->workspaceManager->getRolesWithAccess($workspace)),
                'managers' => array_map(function (User $manager) {
                    return $this->serializer->serialize($manager, [Options::SERIALIZE_MINIMAL]);
                }, $this->workspaceManager->getManagers($workspace)),
                'organizations' => array_map(function ($organization) {
                    return $this->serializer->serialize($organization);
                }, $workspace->getOrganizations()->toArray()),
            ]);
        }

        // maybe do the same for users one day
        if (in_array(Options::WORKSPACE_FETCH_GROUPS, $options)) {
            $groups = $this->om
                ->getRepository('Claroline\CoreBundle\Entity\Group')
                ->findBy(['workspace' => $workspace]);

            $serialized['groups'] = array_map(function (Group $group) {
                return $this->serializer->serialize($group, [Options::SERIALIZE_MINIMAL, Options::NO_COUNT]);
            }, $groups);
        }

        return $serialized;
    }

    /**
     * @param Workspace $workspace
     * @param array     $options
     *
     * @return array
     */
    private function getMeta(Workspace $workspace, array $options)
    {
        $data = [
            'slug' => $workspace->getSlug(),
            'model' => $workspace->isModel(),
            'personal' => $workspace->isPersonal(),
            'description' => $workspace->getDescription(),
            'created' => DateNormalizer::normalize($workspace->getCreated()),
            'updated' => DateNormalizer::normalize($workspace->getCreated()), // todo implement
            'creator' => $workspace->getCreator() ? $this->serializer->serialize($workspace->getCreator(), [Options::SERIALIZE_MINIMAL]) : null,
        ];

        if (!in_array(Options::NO_COUNT, $options)) {
            // this query is very slow
            $data['totalUsers'] = $this->workspaceManager->countUsers($workspace, true);
            $data['totalResources'] = $this->workspaceManager->countResources($workspace);
            $data['usedStorage'] = $this->workspaceManager->getUsedStorage($workspace);
        }

        return $data;
    }

    private function getOpening(Workspace $workspace)
    {
        // todo implement

        return [
            'type' => 'tool',
            'target' => 'home',
        ];
    }

    /**
     * @param Workspace $workspace
     *
     * @return array
     */
    private function getDisplay(Workspace $workspace)
    {
        $options = $this->workspaceManager->getWorkspaceOptions($workspace)->getDetails();

        $openResource = null;
        if (isset($options['workspace_opening_resource']) && $options['workspace_opening_resource']) {
            $resource = $this->om
                ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceNode')
                ->findOneBy(['id' => $options['workspace_opening_resource']]);

            if (!empty($resource)) {
                $openResource = $this->serializer->serialize($resource);
            }
        }

        return [
            'color' => !empty($options['background_color']) ? $options['background_color'] : null,
            'showTools' => !isset($options['hide_tools_menu']) || !$options['hide_tools_menu'],
            'showBreadcrumbs' => !isset($options['hide_breadcrumb']) || !$options['hide_breadcrumb'],
            'openResource' => $openResource,
        ];
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
            'dates' => DateRangeNormalizer::normalize(
                $workspace->getStartDate(),
                $workspace->getEndDate()
            ),
            'maxUsers' => $workspace->getMaxUsers(),
            // TODO : store raw file size to avoid this
            'maxStorage' => $this->utilities->getRealFileSize($workspace->getMaxStorageSize()),
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

    private function getNotifications(Workspace $workspace)
    {
        return [
            'enabled' => !$workspace->isDisabledNotifications(),
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

        if (isset($data['extra']) && isset($data['extra']['model'])) {
            $workspace->setWorkspaceModel($this->serializer->deserialize(
              'Claroline\CoreBundle\Entity\Workspace\Workspace',
              $data['extra']['model']
            ));
        }

        $this->sipe('uuid', 'setUuid', $data, $workspace);
        $this->sipe('code', 'setCode', $data, $workspace);
        $this->sipe('name', 'setName', $data, $workspace);

        $this->sipe('meta.model', 'setIsModel', $data, $workspace);
        $this->sipe('meta.description', 'setDescription', $data, $workspace);

        $this->sipe('notifications.enabled', 'setNotifications', $data, $workspace);

        $this->sipe('restrictions.hidden', 'setHidden', $data, $workspace);
        $this->sipe('restrictions.maxUsers', 'setMaxUsers', $data, $workspace);
        $this->sipe('restrictions.maxResources', 'setMaxUploadResources', $data, $workspace);
        $this->sipe('registration.validation', 'setRegistrationValidation', $data, $workspace);
        $this->sipe('registration.selfRegistration', 'setSelfRegistration', $data, $workspace);
        $this->sipe('registration.selfUnregistration', 'setSelfUnregistration', $data, $workspace);

        if (!empty($data['restrictions'])) {
            // TODO : store raw file size to avoid this
            if (isset($data['restrictions']['maxStorage'])) {
                $workspace->setMaxStorageSize(
                    $this->utilities->formatFileSize($data['restrictions']['maxStorage'])
                );
            }

            if (isset($data['restrictions']['dates'])) {
                $dateRange = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

                $workspace->setStartDate($dateRange[0]);
                $workspace->setEndDate($dateRange[1]);
            }
        }

        if (isset($data['display'])) {
            $workspaceOptions = $this->workspaceManager->getWorkspaceOptions($workspace);
            $workspaceOptions->setDetails([
                'background_color' => !empty($data['display']['color']) ? $data['display']['color'] : null,
                'hide_tools_menu' => !$data['display']['showTools'],
                'hide_breadcrumb' => !$data['display']['showBreadcrumbs'],
                'use_workspace_opening_resource' => !empty($data['display']['openResource']),
                'workspace_opening_resource' => !empty($data['display']['openResource']) ? !empty($data['display']['openResource']['autoId']) : null,
            ]);
        }

        return $workspace;
    }
}
