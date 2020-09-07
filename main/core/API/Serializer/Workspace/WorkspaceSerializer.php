<?php

namespace Claroline\CoreBundle\API\Serializer\Workspace;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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

    /** @var FileUtilities */
    private $fileUt;

    /** @var FinderProvider */
    private $finder;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var PublicFileSerializer */
    private $publicFileSerializer;

    /** @var ResourceNodeSerializer */
    private $resNodeSerializer;

    /**
     * WorkspaceSerializer constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param TokenStorageInterface         $tokenStorage
     * @param ObjectManager                 $om
     * @param WorkspaceManager              $workspaceManager
     * @param ResourceManager               $resourceManager
     * @param FileUtilities                 $fileUt
     * @param FinderProvider                $finder
     * @param UserSerializer                $userSerializer
     * @param PublicFileSerializer          $publicFileSerializer
     * @param ResourceNodeSerializer        $resNodeSerializer
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        WorkspaceManager $workspaceManager,
        ResourceManager $resourceManager,
        FileUtilities $fileUt,
        FinderProvider $finder,
        UserSerializer $userSerializer,
        PublicFileSerializer $publicFileSerializer,
        ResourceNodeSerializer $resNodeSerializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->om = $om;
        $this->workspaceManager = $workspaceManager;
        $this->resourceManager = $resourceManager;
        $this->fileUt = $fileUt;
        $this->finder = $finder;
        $this->userSerializer = $userSerializer;
        $this->publicFileSerializer = $publicFileSerializer;
        $this->resNodeSerializer = $resNodeSerializer;
    }

    public function getName()
    {
        return 'workspace';
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
        $thumbnail = null;
        if ($workspace->getThumbnail()) {
            /** @var PublicFile $thumbnail */
            $thumbnail = $this->om->getRepository(PublicFile::class)->findOneBy([
                'url' => $workspace->getThumbnail(),
            ]);
        }

        $poster = null;
        if ($workspace->getPoster()) {
            /** @var PublicFile $poster */
            $poster = $this->om->getRepository(PublicFile::class)->findOneBy([
                'url' => $workspace->getPoster(),
            ]);
        }

        $serialized = [
            'id' => $this->getUuid($workspace, $options),
            'autoId' => $workspace->getId(),
            'name' => $workspace->getName(),
            'code' => $workspace->getCode(),
            'slug' => $workspace->getSlug(),
            'thumbnail' => $thumbnail ? $this->publicFileSerializer->serialize($thumbnail) : null,
            'poster' => $poster ? $this->publicFileSerializer->serialize($poster) : null,
            'permissions' => [ // TODO it will decrease perfs, should be tested, but it is required in lists
                'open' => $this->authorization->isGranted('OPEN', $workspace),
                'delete' => $this->authorization->isGranted('DELETE', $workspace),
                'configure' => $this->authorization->isGranted('EDIT', $workspace),
                'administrate' => $this->authorization->isGranted('EDIT', $workspace),
                'export' => $this->authorization->isGranted('EXPORT', $workspace),
            ],
            'meta' => $this->getMeta($workspace, $options),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'registered' => $this->isRegistered($workspace),
                'opening' => $this->getOpening($workspace),
                'display' => $this->getDisplay($workspace),
                'breadcrumb' => $this->getBreadcrumb($workspace),
                'restrictions' => $this->getRestrictions($workspace),
                'registration' => $this->getRegistration($workspace, $options),
                'notifications' => $this->getNotifications($workspace),
            ]);

            // TODO : remove me. Used by workspace transfer
            if (!in_array(Options::SERIALIZE_LIST, $options)) {
                $workspaceRoles = array_values(array_unique(array_merge($this->workspaceManager->getRolesWithAccess($workspace), $workspace->getRoles()->toArray())));
                if (in_array(Options::REFRESH_UUID, $options)) {
                    $serialized['roles'] = array_map(function (Role $role) {
                        return [
                          'translationKey' => $role->getTranslationKey(),
                          'type' => $role->getType(),
                        ];
                    }, $workspaceRoles);
                } else {
                    $serialized['roles'] = array_map(function (Role $role) {
                        return [
                            'id' => $role->getUuid(),
                            'name' => $role->getName(),
                            'type' => $role->getType(), // TODO : should be a string for better data readability
                            'translationKey' => $role->getTranslationKey(),
                        ];
                    }, $workspaceRoles);
                }
            }
        }

        return $serialized;
    }

    private function isRegistered(Workspace $workspace)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof User) {
            return $this->workspaceManager->isRegistered($workspace, $user);
        }

        return false;
    }

    /**
     * @param Workspace $workspace
     * @param array     $options
     *
     * @return array
     */
    private function getMeta(Workspace $workspace, array $options)
    {
        return [
            'lang' => $workspace->getLang(),
            'forceLang' => (bool) $workspace->getLang(),
            'archived' => $workspace->isArchived(),
            'model' => $workspace->isModel(),
            'personal' => $workspace->isPersonal(),
            'description' => $workspace->getDescription(),
            'created' => DateNormalizer::normalize($workspace->getCreated()),
            'updated' => DateNormalizer::normalize($workspace->getCreated()), // todo implement
            'creator' => $workspace->getCreator() ? $this->userSerializer->serialize($workspace->getCreator(), [Options::SERIALIZE_MINIMAL]) : null,
        ];
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
            /** @var ResourceNode $resource */
            $resource = $this->om
                ->getRepository(ResourceNode::class)
                ->findOneBy(['id' => $details['workspace_opening_resource']]);

            if (!empty($resource)) {
                $openingData['target'] = $this->resNodeSerializer->serialize($resource, [Options::SERIALIZE_MINIMAL]);
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
        $options = $workspace->getOptions()->getDetails();

        $openResource = null;
        if (isset($options['workspace_opening_resource']) && $options['workspace_opening_resource']) {
            $resource = $this->om
                ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceNode')
                ->findOneBy(['id' => $options['workspace_opening_resource']]);

            if (!empty($resource)) {
                $openResource = $this->resNodeSerializer->serialize($resource);
            }
        }

        return [
            'color' => !empty($options['background_color']) ? $options['background_color'] : null,
            'showMenu' => !isset($options['hide_tools_menu']) || !$options['hide_tools_menu'],
            'showProgression' => $workspace->getShowProgression(),
            'openResource' => $openResource,
        ];
    }

    private function getBreadcrumb(Workspace $workspace)
    {
        $options = $workspace->getOptions();

        return [
            'displayed' => $options->getShowBreadcrumb(),
            'items' => $options->getBreadcrumbItems(),
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
                $workspace->getAccessibleFrom(),
                $workspace->getAccessibleUntil()
            ),
            'code' => $workspace->getAccessCode(),
            'allowedIps' => $workspace->getAllowedIps(),
            'maxUsers' => $workspace->getMaxUsers(),
            // TODO : store raw file size to avoid this
            'maxStorage' => $this->fileUt->getRealFileSize($workspace->getMaxStorageSize()),
            'maxResources' => $workspace->getMaxUploadResources(),
        ];
    }

    /**
     * @param Workspace $workspace
     * @param array     $options
     *
     * @return array
     */
    private function getRegistration(Workspace $workspace, array $options)
    {
        if ($workspace->getDefaultRole()) {
            if (in_array(Options::REFRESH_UUID, $options)) {
                $defaultRole = [
                  'translationKey' => $workspace->getDefaultRole()->getTranslationKey(),
                  'type' => $workspace->getDefaultRole()->getType(),
                ];
            } else {
                $defaultRole = [
                    'id' => $workspace->getDefaultRole()->getUuid(),
                    'name' => $workspace->getDefaultRole()->getName(),
                    'type' => $workspace->getDefaultRole()->getType(), // TODO : should be a string for better data readability
                    'translationKey' => $workspace->getDefaultRole()->getTranslationKey(),
                ];
            }
        } else {
            $defaultRole = null;
        }

        return [
            'validation' => $workspace->getRegistrationValidation(),
            'waitingForRegistration' => $workspace->getSelfRegistration() ? $this->waitingForRegistration($workspace) : false,
            'selfRegistration' => $workspace->getSelfRegistration(),
            'selfUnregistration' => $workspace->getSelfUnregistration(),
            'defaultRole' => $defaultRole,
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
        $this->sipe('code', 'setCode', $data, $workspace);
        $this->sipe('name', 'setName', $data, $workspace);

        if (isset($data['thumbnail']) && isset($data['thumbnail']['id'])) {
            /** @var PublicFile $thumbnail */
            $thumbnail = $this->om->getObject($data['thumbnail'], PublicFile::class);
            $workspace->setThumbnail($data['thumbnail']['url']);
            $this->fileUt->createFileUse($thumbnail, Workspace::class, $workspace->getUuid());
        }

        if (isset($data['poster']) && isset($data['poster']['id'])) {
            /** @var PublicFile $poster */
            $poster = $this->om->getObject($data['poster'], PublicFile::class);
            $workspace->setPoster($data['poster']['url']);
            $this->fileUt->createFileUse($poster, Workspace::class, $workspace->getUuid());
        }

        if (empty($workspace->getCreator()) && isset($data['meta']) && !empty($data['meta']['creator'])) {
            /** @var User $creator */
            $creator = $this->om->getObject($data['meta']['creator'], User::class);
            $workspace->setCreator($creator);
        }

        if (isset($data['extra']) && isset($data['extra']['model']) && isset($data['extra']['model']['code'])) {
            /** @var Workspace $model */
            $model = $this->om->getRepository(Workspace::class)->findOneBy(['code' => $data['extra']['model']['code']]);
            $workspace->setWorkspaceModel($model);
        }

        //not sure if keep that. Might be troublesome later for rich texts
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $workspace);
        } else {
            $workspace->refreshUuid();
        }

        if (isset($data['meta'])) {
            $this->sipe('meta.model', 'setModel', $data, $workspace);
            $this->sipe('meta.description', 'setDescription', $data, $workspace);
            $this->sipe('meta.lang', 'setLang', $data, $workspace);

            if (empty($workspace->getCreated()) && !empty($data['meta']['created'])) {
                $date = DateNormalizer::denormalize($data['meta']['created']);
                $workspace->setCreated($date->getTimestamp());
            }
        }

        $this->sipe('notifications.enabled', 'setNotifications', $data, $workspace);

        $this->sipe('restrictions.hidden', 'setHidden', $data, $workspace);
        $this->sipe('restrictions.code', 'setAccessCode', $data, $workspace);
        $this->sipe('restrictions.allowedIps', 'setAllowedIps', $data, $workspace);

        $this->sipe('registration.validation', 'setRegistrationValidation', $data, $workspace);
        $this->sipe('registration.selfRegistration', 'setSelfRegistration', $data, $workspace);
        $this->sipe('registration.selfUnregistration', 'setSelfUnregistration', $data, $workspace);

        if (isset($data['registration']) && isset($data['registration']['defaultRole'])) {
            /** @var Role $defaultRole */
            $defaultRole = $this->om->getObject($data['registration']['defaultRole'], Role::class);
            $workspace->setDefaultRole($defaultRole);
        }

        if (!empty($data['restrictions'])) {
            // TODO : store raw file size to avoid this
            if (isset($data['restrictions']['maxStorage'])) {
                $workspace->setMaxStorageSize(
                    $this->fileUt->formatFileSize($data['restrictions']['maxStorage'] ?? 0)
                );
            }

            if (isset($data['restrictions']['maxUsers'])) {
                $workspace->setMaxUsers($data['restrictions']['maxUsers'] ?? 0);
            }

            if (isset($data['restrictions']['maxResources'])) {
                $workspace->setMaxUploadResources($data['restrictions']['maxResources'] ?? 0);
            }

            if (isset($data['restrictions']['dates'])) {
                $dateRange = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

                $workspace->setAccessibleFrom($dateRange[0]);
                $workspace->setAccessibleUntil($dateRange[1]);
            }
        }

        $workspaceOptions = $workspace->getOptions();

        if (isset($data['display']) || isset($data['opening'])) {
            $this->sipe('display.showProgression', 'setShowProgression', $data, $workspace);

            $details = $workspaceOptions->getDetails();
            if (empty($details)) {
                $details = [];
            }

            if (isset($data['display'])) {
                $details['background_color'] = !empty($data['display']['color']) ? $data['display']['color'] : null;
                $details['hide_tools_menu'] = isset($data['display']['showMenu']) ? !$data['display']['showMenu'] : true;
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

        if (isset($data['breadcrumb'])) {
            if (isset($data['breadcrumb']['displayed'])) {
                $workspaceOptions->setShowBreadcrumb($data['breadcrumb']['displayed']);
            }

            if (isset($data['breadcrumb']['items'])) {
                $workspaceOptions->setBreadcrumbItems($data['breadcrumb']['items']);
            }
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
