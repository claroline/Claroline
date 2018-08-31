<?php

namespace Claroline\CoreBundle\API\Serializer\Workspace;

use Claroline\AppBundle\API\FinderProvider;
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
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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

    /** @var AuthorizationCheckerInterface */
    private $tokenStorage;

    /** @var ObjectManager */
    private $om;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /** @var ResourceManager */
    private $resourceManager;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ClaroUtilities */
    private $utilities;

    /** @var FileUtilities */
    private $fileUt;

    /** @var FinderProvider */
    private $finder;

    /**
     * WorkspaceSerializer constructor.
     *
     * @DI\InjectParams({
     *     "authorization"    = @DI\Inject("security.authorization_checker"),
     *     "om"               = @DI\Inject("claroline.persistence.object_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "resourceManager"  = @DI\Inject("claroline.manager.resource_manager"),
     *     "serializer"       = @DI\Inject("claroline.api.serializer"),
     *     "utilities"        = @DI\Inject("claroline.utilities.misc"),
     *     "fileUt"           = @DI\Inject("claroline.utilities.file"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "finder"           = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ObjectManager                 $om
     * @param WorkspaceManager              $workspaceManager
     * @param ResourceManager               $resourceManager
     * @param SerializerProvider            $serializer
     * @param ClaroUtilities                $utilities
     * @param FileUtilities                 $fileUt
     * @param TokenStorageInterface         $tokenStorage
     * @param FinderProvider                $finder
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        WorkspaceManager $workspaceManager,
        ResourceManager $resourceManager,
        SerializerProvider $serializer,
        ClaroUtilities $utilities,
        FileUtilities $fileUt,
        FinderProvider $finder
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->om = $om;
        $this->workspaceManager = $workspaceManager;
        $this->resourceManager = $resourceManager;
        $this->serializer = $serializer;
        $this->utilities = $utilities;
        $this->fileUt = $fileUt;
        $this->finder = $finder;
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
                    'administrate' => $this->authorization->isGranted('EDIT', $workspace),
                    'export' => $this->authorization->isGranted('EXPORT', $workspace),
                ],
                'meta' => $this->getMeta($workspace, $options),
                'opening' => $this->getOpening($workspace, $options),
                'display' => $this->getDisplay($workspace),
                'restrictions' => $this->getRestrictions($workspace),
                'registration' => $this->getRegistration($workspace),
                'notifications' => $this->getNotifications($workspace),
            ]);

            if (!in_array(Options::SERIALIZE_LIST, $options)) {
                $serialized['roles'] = array_map(function (Role $role) {
                    return $this->serializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
                }, $this->workspaceManager->getRolesWithAccess($workspace));
                $serialized['managers'] = array_map(function (User $manager) {
                    return $this->serializer->serialize($manager, [Options::SERIALIZE_MINIMAL]);
                }, $this->workspaceManager->getManagers($workspace));
                $serialized['organizations'] = array_map(function ($organization) {
                    return $this->serializer->serialize($organization);
                }, $workspace->getOrganizations()->toArray());
            }
        }

        // maybe do the same for users one day
        if (in_array(Options::WORKSPACE_FETCH_GROUPS, $options)) {
            $groups = $this->om
                ->getRepository('Claroline\CoreBundle\Entity\Group')
                ->findByWorkspace($workspace);

            $serialized['groups'] = array_map(function (Group $group) {
                return $this->serializer->serialize($group, [Options::SERIALIZE_MINIMAL]);
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
            'lang' => $workspace->getLang(),
            'forceLang' => (bool) $workspace->getLang(),
            'slug' => $workspace->getSlug(),
            'model' => $workspace->isModel(),
            'personal' => $workspace->isPersonal(),
            'description' => $workspace->getDescription(),
            'created' => DateNormalizer::normalize($workspace->getCreated()),
            'updated' => DateNormalizer::normalize($workspace->getCreated()), // todo implement
            'creator' => $workspace->getCreator() ? $this->serializer->serialize($workspace->getCreator(), [Options::SERIALIZE_MINIMAL]) : null,
        ];

        if (!in_array(Options::SERIALIZE_LIST, $options)) {
            // this query is very slow
            $data['totalUsers'] = $this->finder->fetch(
              User::class,
              ['workspace' => $workspace->getUuid()],
              null,
              0,
              -1,
              true
            );
            $data['totalResources'] = $this->workspaceManager->countResources($workspace);
            $data['usedStorage'] = $this->workspaceManager->getUsedStorage($workspace);
        }

        return $data;
    }

    private function getOpening(Workspace $workspace)
    {
        $details = $this->workspaceManager->getWorkspaceOptions($workspace)->getDetails();
        $openingData = [
            'type' => 'tool',
            'target' => 'home',
        ];

        if ($details && isset($details['opening_type'])) {
            $openingData['type'] = $details['opening_type'];
        }
        if ($details && isset($details['opening_target'])) {
            $openingData['target'] = $details['opening_target'];
        }
        if ('resource' === $openingData['type'] && isset($details['workspace_opening_resource']) && $details['workspace_opening_resource']) {
            $resource = $this->om
                ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceNode')
                ->findOneBy(['id' => $details['workspace_opening_resource']]);

            if (!empty($resource)) {
                $openingData['target'] = $this->serializer->serialize($resource);
            }
        }

        return $openingData;
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
            'waitingForRegistration' => $workspace->getSelfRegistration() ? $this->waitingForRegistration($workspace) : false,
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

        $this->sipe('meta.model', 'setModel', $data, $workspace);
        $this->sipe('meta.description', 'setDescription', $data, $workspace);
        $this->sipe('meta.lang', 'setLang', $data, $workspace);

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

        if (isset($data['display']) || isset($data['opening'])) {
            $workspaceOptions = $this->workspaceManager->getWorkspaceOptions($workspace);
            $details = $workspaceOptions->getDetails();

            if (empty($details)) {
                $details = [];
            }
            if (isset($data['display'])) {
                $details['background_color'] = !empty($data['display']['color']) ? $data['display']['color'] : null;
                $details['hide_tools_menu'] = isset($data['display']['showTools']) ? !$data['display']['showTools'] : true;
                $details['hide_breadcrumb'] = isset($data['display']['showBreadcrumbs']) ? !$data['display']['showBreadcrumbs'] : true;
                $details['use_workspace_opening_resource'] = !empty($data['display']['openResource']);
                $details['workspace_opening_resource'] = !empty($data['display']['openResource']) && !empty($data['display']['openResource']['autoId']) ?
                    $data['display']['openResource']['autoId'] :
                    null;
            }
            if (isset($data['opening'])) {
                $details['opening_type'] = isset($data['opening']['type']) && isset($data['opening']['target']) && !empty($data['opening']['target']) ?
                    $data['opening']['type'] :
                    'tool';
                $details['opening_target'] = isset($data['opening']['target']) && !empty($data['opening']['target']) ?
                    $data['opening']['target'] :
                    'home';

                if ('resource' === $data['opening']['type'] && isset($data['opening']['target']['autoId'])) {
                    $details['workspace_opening_resource'] = $data['opening']['target']['autoId'];
                    $details['use_workspace_opening_resource'] = true;
                } else {
                    $details['workspace_opening_resource'] = null;
                    $details['use_workspace_opening_resource'] = false;
                }
            }
            $workspaceOptions->setDetails($details);
        }

        return $workspace;
    }

    private function waitingForRegistration(Workspace $workspace)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ('anon.' === $user) {
            return false;
        }

        return (bool) $this->om->getRepository('Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue')
          ->findBy(['workspace' => $workspace, 'user' => $user]);
    }
}
