<?php

namespace Claroline\CoreBundle\API\Serializer\Workspace;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\User\OrganizationSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WorkspaceSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var AuthorizationCheckerInterface */
    private $tokenStorage;

    private $eventDispatcher;

    /** @var ObjectManager */
    private $om;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /** @var OrganizationSerializer */
    private $organizationSerializer;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var PublicFileSerializer */
    private $publicFileSerializer;

    /** @var ResourceNodeSerializer */
    private $resNodeSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        ObjectManager $om,
        WorkspaceManager $workspaceManager,
        UserSerializer $userSerializer,
        OrganizationSerializer $organizationSerializer,
        PublicFileSerializer $publicFileSerializer,
        ResourceNodeSerializer $resNodeSerializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->workspaceManager = $workspaceManager;
        $this->userSerializer = $userSerializer;
        $this->organizationSerializer = $organizationSerializer;
        $this->publicFileSerializer = $publicFileSerializer;
        $this->resNodeSerializer = $resNodeSerializer;
    }

    public function getClass()
    {
        return Workspace::class;
    }

    public function getName()
    {
        return 'workspace';
    }

    public function getSchema()
    {
        return '#/main/core/workspace.json';
    }

    public function getSamples()
    {
        return '#/main/core/workspace';
    }

    /**
     * Serializes a Workspace entity for the JSON api.
     */
    public function serialize(Workspace $workspace, array $options = []): array
    {
        $thumbnail = null;
        if ($workspace->getThumbnail()) {
            /** @var PublicFile $thumbnail */
            $thumbnail = $this->om->getRepository(PublicFile::class)->findOneBy([
                'url' => $workspace->getThumbnail(),
            ]);
        }

        $serialized = [
            'id' => $this->getUuid($workspace, $options),
            'autoId' => $workspace->getId(),
            'name' => $workspace->getName(),
            'code' => $workspace->getCode(),
            'slug' => $workspace->getSlug(),
            'thumbnail' => $thumbnail ? $this->publicFileSerializer->serialize($thumbnail) : null,
            'meta' => $this->getMeta($workspace, $options),
            'contactEmail' => $workspace->getContactEmail(),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $editPerm = $this->authorization->isGranted('EDIT', $workspace);

            $serialized['permissions'] = [
                'open' => $this->authorization->isGranted('OPEN', $workspace),
                'delete' => $this->authorization->isGranted('DELETE', $workspace),
                'configure' => $editPerm,
                'administrate' => $editPerm,
                'export' => $this->authorization->isGranted('EXPORT', $workspace),
                'archive' => $this->authorization->isGranted('ARCHIVE', $workspace),
            ];
        }

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            $poster = null;
            if ($workspace->getPoster()) {
                /** @var PublicFile $poster */
                $poster = $this->om->getRepository(PublicFile::class)->findOneBy([
                    'url' => $workspace->getPoster(),
                ]);
            }

            $serialized = array_merge($serialized, [
                'tags' => $this->serializeTags($workspace),
                'poster' => $poster ? $this->publicFileSerializer->serialize($poster) : null,
                'opening' => $this->getOpening($workspace),
                'display' => $this->getDisplay($workspace),
                'breadcrumb' => $this->getBreadcrumb($workspace),
                'restrictions' => $this->getRestrictions($workspace),
                'registration' => $this->getRegistration($workspace, $options),
                'notifications' => $this->getNotifications($workspace),
            ]);

            if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
                $serialized['registered'] = $this->isRegistered($workspace);
            }

            if (!in_array(SerializerInterface::SERIALIZE_LIST, $options)) {
                $serialized['organizations'] = array_map(function (Organization $organization) {
                    return $this->organizationSerializer->serialize($organization, [SerializerInterface::SERIALIZE_MINIMAL]);
                }, $workspace->getOrganizations()->toArray());
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

    private function getMeta(Workspace $workspace, array $options): array
    {
        return [
            'lang' => $workspace->getLang(),
            'archived' => $workspace->isArchived(),
            'model' => $workspace->isModel(),
            'personal' => $workspace->isPersonal(),
            'description' => $workspace->getDescription(),
            'created' => DateNormalizer::normalize($workspace->getCreatedAt()),
            'updated' => DateNormalizer::normalize($workspace->getUpdatedAt()),
            'creator' => $workspace->getCreator() ? $this->userSerializer->serialize($workspace->getCreator(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
        ];
    }

    private function getOpening(Workspace $workspace)
    {
        $details = $this->workspaceManager->getWorkspaceOptions($workspace)->getDetails();
        $openingData = [
            'type' => 'tool',
            'target' => 'home',
            'menu' => null,
        ];

        if ($details && isset($details['hide_tools_menu'])) {
            // test for bool values is for retro-compatibility to avoid having to migrate a json col in a huge table
            if (is_string($details['hide_tools_menu'])) {
                $openingData['menu'] = $details['hide_tools_menu'];
            } else {
                $openingData['menu'] = !$details['hide_tools_menu'] ? 'open' : 'close';
            }
        }

        if ($details && isset($details['opening_type'])) {
            $openingData['type'] = $details['opening_type'];
        }
        if ($details && isset($details['opening_target'])) {
            $openingData['target'] = $details['opening_target'];
        }
        if ('resource' === $openingData['type'] && isset($details['workspace_opening_resource']) && $details['workspace_opening_resource']) {
            /** @var ResourceNode $resource */
            $resource = $this->om->find(ResourceNode::class, $details['workspace_opening_resource']);

            if (!empty($resource)) {
                $openingData['target'] = $this->resNodeSerializer->serialize($resource, [SerializerInterface::SERIALIZE_MINIMAL]);
            }
        }

        return $openingData;
    }

    private function getDisplay(Workspace $workspace): array
    {
        $options = $workspace->getOptions()->getDetails();

        return [
            'color' => !empty($options['background_color']) ? $options['background_color'] : null,
            'showProgression' => $workspace->getShowProgression(),
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

    private function getRestrictions(Workspace $workspace): array
    {
        return [
            'hidden' => $workspace->isHidden(),
            'dates' => DateRangeNormalizer::normalize(
                $workspace->getAccessibleFrom(),
                $workspace->getAccessibleUntil()
            ),
            'code' => $workspace->getAccessCode(),
            'allowedIps' => $workspace->getAllowedIps(),
        ];
    }

    private function getRegistration(Workspace $workspace, array $options): array
    {
        $defaultRole = null;
        if ($workspace->getDefaultRole()) {
            // this should use RoleSerializer but we will get a circular reference if we do it
            $defaultRole = [
                'id' => $workspace->getDefaultRole()->getUuid(),
                'name' => $workspace->getDefaultRole()->getName(),
                'type' => $workspace->getDefaultRole()->getType(), // TODO : should be a string for better data readability
                'translationKey' => $workspace->getDefaultRole()->getTranslationKey(),
            ];
        }

        $serialized = [
            'validation' => $workspace->getRegistrationValidation(),
            'selfRegistration' => $workspace->getSelfRegistration(),
            'selfUnregistration' => $workspace->getSelfUnregistration(),
            'defaultRole' => $defaultRole,
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $serialized['waitingForRegistration'] = $workspace->getSelfRegistration() ? $this->waitingForRegistration($workspace) : false;
        }

        return $serialized;
    }

    private function getNotifications(Workspace $workspace)
    {
        return [
            'enabled' => $workspace->hasNotifications(),
        ];
    }

    private function serializeTags(Workspace $workspace)
    {
        $event = new GenericDataEvent([
            'class' => Workspace::class,
            'ids' => [$workspace->getUuid()],
        ]);
        $this->eventDispatcher->dispatch($event, 'claroline_retrieve_used_tags_by_class_and_ids');

        return $event->getResponse() ?? [];
    }

    /**
     * Deserializes Workspace data into entities.
     */
    public function deserialize(array $data, Workspace $workspace, array $options = []): Workspace
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $workspace);
        } else {
            $workspace->refreshUuid();
        }

        if (!empty($data['model'])) {
            /** @var Workspace $model */
            $model = $this->om->getObject($data['model'], Workspace::class, ['code']);
            if (!empty($model)) {
                $workspace->setWorkspaceModel($model);
            }
        }

        $this->sipe('code', 'setCode', $data, $workspace);
        $this->sipe('name', 'setName', $data, $workspace);
        $this->sipe('contactEmail', 'setContactEmail', $data, $workspace);

        if (array_key_exists('thumbnail', $data)) {
            $thumbnailUrl = null;
            if (!empty($data['thumbnail']) && !empty($data['thumbnail']['url'])) {
                $thumbnailUrl = $data['thumbnail']['url'];
            }

            $workspace->setThumbnail($thumbnailUrl);
        }

        if (array_key_exists('poster', $data)) {
            $posterUrl = null;
            if (!empty($data['poster']) && !empty($data['poster']['url'])) {
                $posterUrl = $data['poster']['url'];
            }

            $workspace->setPoster($posterUrl);
        }

        if (isset($data['meta'])) {
            $this->sipe('meta.personal', 'setPersonal', $data, $workspace);
            $this->sipe('meta.model', 'setModel', $data, $workspace);
            $this->sipe('meta.description', 'setDescription', $data, $workspace);
            $this->sipe('meta.lang', 'setLang', $data, $workspace);

            if (array_key_exists('created', $data['meta'])) {
                $workspace->setCreatedAt(DateNormalizer::denormalize($data['meta']['created']));
            }
            if (array_key_exists('updated', $data['meta'])) {
                $workspace->setUpdatedAt(DateNormalizer::denormalize($data['meta']['updated']));
            }

            if (array_key_exists('creator', $data['meta'])) {
                $creator = null;
                if (!empty($data['meta']['creator'])) {
                    /** @var User $creator */
                    $creator = $this->om->getObject($data['meta']['creator'], User::class);
                }

                $workspace->setCreator($creator);
            }
        }

        $this->sipe('notifications.enabled', 'setNotifications', $data, $workspace);

        if (isset($data['registration'])) {
            $this->sipe('registration.validation', 'setRegistrationValidation', $data, $workspace);
            $this->sipe('registration.selfRegistration', 'setSelfRegistration', $data, $workspace);
            $this->sipe('registration.selfUnregistration', 'setSelfUnregistration', $data, $workspace);

            if (isset($data['registration']['defaultRole'])) {
                /** @var Role $defaultRole */
                $defaultRole = $this->om->getObject($data['registration']['defaultRole'], Role::class);
                $workspace->setDefaultRole($defaultRole);
            }
        }

        if (!empty($data['restrictions'])) {
            $this->sipe('restrictions.hidden', 'setHidden', $data, $workspace);
            $this->sipe('restrictions.code', 'setAccessCode', $data, $workspace);
            $this->sipe('restrictions.allowedIps', 'setAllowedIps', $data, $workspace);

            if (isset($data['restrictions']['dates'])) {
                $dateRange = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

                $workspace->setAccessibleFrom($dateRange[0]);
                $workspace->setAccessibleUntil($dateRange[1]);
            }
        }

        $workspaceOptions = $this->workspaceManager->getWorkspaceOptions($workspace);

        if (isset($data['display']) || isset($data['opening'])) {
            $details = $workspaceOptions->getDetails();
            if (empty($details)) {
                $details = [];
            }

            if (isset($data['display'])) {
                $this->sipe('display.showProgression', 'setShowProgression', $data, $workspace);
                $details['background_color'] = !empty($data['display']['color']) ? $data['display']['color'] : null;
            }

            if (isset($data['opening'])) {
                $details['hide_tools_menu'] = isset($data['opening']['menu']) ? $data['opening']['menu'] : null;
                $details['opening_type'] = isset($data['opening']['type']) && !empty($data['opening']['target']) ?
                    $data['opening']['type'] :
                    'tool';
                $details['opening_target'] = !empty($data['opening']['target']) ?
                    $data['opening']['target'] :
                    'home';

                if ('resource' === $details['opening_type'] && isset($details['opening_target']['id'])) {
                    $details['workspace_opening_resource'] = $details['opening_target']['id'];
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

        if (array_key_exists('organizations', $data)) {
            $organizations = [];
            if (!empty($data['organizations'])) {
                foreach ($data['organizations'] as $organizationData) {
                    if (isset($organizationData['id'])) {
                        $organization = $this->om->getObject($organizationData, Organization::class);
                        if ($organization) {
                            $organizations[] = $organization;
                        }
                    }
                }
            }

            $workspace->setOrganizations($organizations);
        }

        return $workspace;
    }

    private function waitingForRegistration(Workspace $workspace)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return (bool) $this->om->getRepository('Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue')
          ->findBy(['workspace' => $workspace, 'user' => $user]);
    }
}
