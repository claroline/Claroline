<?php

namespace Claroline\CoreBundle\API\Serializer\Workspace;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\OrganizationSerializer;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
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

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly WorkspaceManager $workspaceManager,
        private readonly UserSerializer $userSerializer,
        private readonly OrganizationSerializer $organizationSerializer,
        private readonly ResourceNodeSerializer $resNodeSerializer
    ) {
    }

    public function getClass(): string
    {
        return Workspace::class;
    }

    public function getName(): string
    {
        return 'workspace';
    }

    public function getSchema(): string
    {
        return '#/main/core/workspace.json';
    }

    public function getSamples(): string
    {
        return '#/main/core/workspace';
    }

    /**
     * Serializes a Workspace entity for the JSON api.
     */
    public function serialize(Workspace $workspace, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $workspace->getUuid(),
                'name' => $workspace->getName(),
                'code' => $workspace->getCode(),
                'slug' => $workspace->getSlug(),
                'thumbnail' => $workspace->getThumbnail(),
                'meta' => [
                    'model' => $workspace->isModel(),
                    'description' => $workspace->getDescription(),
                ],
            ];
        }

        $serialized = [
            'id' => $workspace->getUuid(),
            'autoId' => $workspace->getId(),
            'name' => $workspace->getName(),
            'code' => $workspace->getCode(),
            'slug' => $workspace->getSlug(),
            'thumbnail' => $workspace->getThumbnail(),
            'poster' => $workspace->getPoster(),
            'meta' => [
                'archived' => $workspace->isArchived(),
                'public' => $workspace->isPublic(),
                'model' => $workspace->isModel(),
                'personal' => $workspace->isPersonal(),
                'description' => $workspace->getDescription(),
                'descriptionHtml' => $workspace->getDescriptionHtml(),
                'created' => DateNormalizer::normalize($workspace->getCreatedAt()),
                'updated' => DateNormalizer::normalize($workspace->getUpdatedAt()),
                'creator' => $workspace->getCreator() ? $this->userSerializer->serialize($workspace->getCreator(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            ],
            'contactEmail' => $workspace->getContactEmail(),
            'tags' => $this->serializeTags($workspace),
            'opening' => $this->getOpening($workspace),
            'restrictions' => [
                'hidden' => $workspace->isHidden(),
                'dates' => DateRangeNormalizer::normalize($workspace->getAccessibleFrom(), $workspace->getAccessibleUntil()),
                'code' => $workspace->getAccessCode(),
            ],
            'registration' => $this->getRegistration($workspace, $options),
            'evaluation' => [
                'successCondition' => $workspace->getSuccessCondition(),
                'estimatedDuration' => $workspace->getEstimatedDuration(),
                'scoreTotal' => $workspace->getScoreTotal(),
            ],
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $administrate = $this->authorization->isGranted('ADMINISTRATE', $workspace);

            $serialized['permissions'] = [
                'open' => $administrate || $this->authorization->isGranted('OPEN', $workspace),
                'delete' => $administrate || $this->authorization->isGranted('DELETE', $workspace),
                'edit' => $administrate || $this->authorization->isGranted('EDIT', $workspace),
                'administrate' => $administrate,
                'export' => $administrate || $this->authorization->isGranted('EXPORT', $workspace),
                'archive' => $administrate || $this->authorization->isGranted('ARCHIVE', $workspace),
            ];

            // this is a huge performances bottleneck as it will check if the current user as at least one right on one ws tool
            // $serialized['registered'] = $this->isRegistered($workspace);
        }

        /*if (!in_array(SerializerInterface::SERIALIZE_LIST, $options)) {
            $serialized['organizations'] = array_map(function (Organization $organization) {
                return $this->organizationSerializer->serialize($organization, [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $workspace->getOrganizations()->toArray());
        }*/

        return $serialized;
    }

    private function getOpening(Workspace $workspace): array
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

    private function getRegistration(Workspace $workspace, array $options): array
    {
        /*$defaultRole = null;
        if ($workspace->getDefaultRole()) {
            // this should use RoleSerializer, but we will get a circular reference if we do it
            $defaultRole = [
                'id' => $workspace->getDefaultRole()->getUuid(),
                'name' => $workspace->getDefaultRole()->getName(),
                'type' => $workspace->getDefaultRole()->getType(),
                'translationKey' => $workspace->getDefaultRole()->getTranslationKey(),
            ];
        }*/

        $serialized = [
            'validation' => $workspace->getRegistrationValidation(),
            'selfRegistration' => $workspace->getSelfRegistration(),
            'selfUnregistration' => $workspace->getSelfUnregistration(),
            // 'defaultRole' => $defaultRole,
            'maxTeams' => $workspace->getMaxTeams(),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            // this is a huge performances bottleneck
            // $serialized['waitingForRegistration'] = $workspace->getSelfRegistration() && $workspace->getRegistrationValidation() ? $this->waitingForRegistration($workspace) : false;
        }

        return $serialized;
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
        $this->sipe('poster', 'setPoster', $data, $workspace);
        $this->sipe('thumbnail', 'setThumbnail', $data, $workspace);

        if (isset($data['meta'])) {
            $this->sipe('meta.personal', 'setPersonal', $data, $workspace);
            $this->sipe('meta.model', 'setModel', $data, $workspace);
            $this->sipe('meta.description', 'setDescription', $data, $workspace);
            $this->sipe('meta.descriptionHtml', 'setDescriptionHtml', $data, $workspace);

            if (array_key_exists('created', $data['meta'])) {
                $workspace->setCreatedAt(DateNormalizer::denormalize($data['meta']['created']));
            }
            if (array_key_exists('updated', $data['meta'])) {
                $workspace->setUpdatedAt(DateNormalizer::denormalize($data['meta']['updated']));
            }

            if (isset($data['meta']['public'])) {
                $workspace->setPublic($data['meta']['public']);
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

        if (isset($data['registration'])) {
            $this->sipe('registration.validation', 'setRegistrationValidation', $data, $workspace);
            $this->sipe('registration.selfRegistration', 'setSelfRegistration', $data, $workspace);
            $this->sipe('registration.selfUnregistration', 'setSelfUnregistration', $data, $workspace);
            $this->sipe('registration.maxTeams', 'setMaxTeams', $data, $workspace);

            if (isset($data['registration']['defaultRole'])) {
                /** @var Role $defaultRole */
                $defaultRole = $this->om->getObject($data['registration']['defaultRole'], Role::class);
                $workspace->setDefaultRole($defaultRole);
            }
        }

        if (!empty($data['restrictions'])) {
            $this->sipe('restrictions.hidden', 'setHidden', $data, $workspace);
            $this->sipe('restrictions.code', 'setAccessCode', $data, $workspace);

            if (isset($data['restrictions']['dates'])) {
                $dateRange = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

                $workspace->setAccessibleFrom($dateRange[0]);
                $workspace->setAccessibleUntil($dateRange[1]);
            }
        }

        $workspaceOptions = $this->workspaceManager->getWorkspaceOptions($workspace);

        if (isset($data['opening'])) {
            $details = $workspaceOptions->getDetails();
            if (empty($details)) {
                $details = [];
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

        if (isset($data['evaluation'])) {
            $this->sipe('evaluation.successCondition', 'setSuccessCondition', $data, $workspace);
            $this->sipe('evaluation.estimatedDuration', 'setEstimatedDuration', $data, $workspace);
            $this->sipe('evaluation.scoreTotal', 'setScoreTotal', $data, $workspace);
        }

        if (array_key_exists('organizations', $data)) {
            $organizations = [];
            if (!empty($data['organizations'])) {
                foreach ($data['organizations'] as $organizationData) {
                    if (!empty($organizationData['id']) && empty($organizations[$organizationData['id']])) {
                        /** @var Organization $organization */
                        $organization = $this->om->getObject($organizationData, Organization::class);
                        if ($organization) {
                            $organizations[$organization->getUuid()] = $organization;
                        }
                    }
                }
            }

            $workspace->setOrganizations(array_values($organizations));
        }

        if (isset($data['tags'])) {
            $this->deserializeTags($workspace, $data['tags'], $options);
        }

        return $workspace;
    }

    private function serializeTags(Workspace $workspace): array
    {
        $event = new GenericDataEvent([
            'class' => Workspace::class,
            'ids' => [$workspace->getUuid()],
        ]);
        $this->eventDispatcher->dispatch($event, 'claroline_retrieve_used_tags_by_class_and_ids');

        return $event->getResponse() ?? [];
    }

    private function deserializeTags(Workspace $workspace, array $tags = [], array $options = []): void
    {
        if (in_array(Options::PERSIST_TAG, $options)) {
            $event = new GenericDataEvent([
                'tags' => $tags,
                'data' => [
                    [
                        'class' => Workspace::class,
                        'id' => $workspace->getUuid(),
                        'name' => $workspace->getName(),
                    ],
                ],
                'replace' => true,
            ]);

            $this->eventDispatcher->dispatch($event, 'claroline_tag_multiple_data');
        }
    }
}
