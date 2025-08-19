<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\OrganizationSerializer;
use Claroline\CommunityBundle\Serializer\RoleSerializer;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Facet\PanelFacetSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Repository\CourseRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CourseSerializer
{
    use SerializerTrait;

    private WorkspaceRepository $workspaceRepo;
    private CourseRepository $courseRepo;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly UserSerializer $userSerializer,
        private readonly RoleSerializer $roleSerializer,
        private readonly OrganizationSerializer $orgaSerializer,
        private readonly WorkspaceSerializer $workspaceSerializer,
        private readonly PanelFacetSerializer $panelFacetSerializer,
    ) {
        $this->workspaceRepo = $om->getRepository(Workspace::class);
        $this->courseRepo = $om->getRepository(Course::class);
    }

    public function getClass(): string
    {
        return Course::class;
    }

    public function getSchema(): string
    {
        return '#/plugin/cursus/course.json';
    }

    public function serialize(Course $course, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $course->getUuid(),
                'name' => $course->getName(),
                'code' => $course->getCode(),
                'slug' => $course->getSlug(),
                'poster' => $course->getPoster(),
                'plainDescription' => $course->getPlainDescription(),
            ];
        }

        $serialized = [
            'id' => $course->getUuid(),
            'name' => $course->getName(),
            'code' => $course->getCode(),
            'slug' => $course->getSlug(),
            'poster' => $course->getPoster(),
            'description' => $course->getDescription(),
            'plainDescription' => $course->getPlainDescription(),
            'meta' => [
                'creator' => $course->getCreator() ?
                    $this->userSerializer->serialize($course->getCreator(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
                'created' => DateNormalizer::normalize($course->getCreatedAt()),
                'updated' => DateNormalizer::normalize($course->getUpdatedAt()),
                'duration' => $course->getDefaultSessionDuration(),
                'public' => $course->isPublic(),
                'archived' => $course->isArchived(),
            ],
            'certification' => $course->getCertification(),
            'objectives' => $course->getObjectives(),
            'requirements' => $course->getRequirements(),
            'targetAudience' => $course->getTargetAudience(),
            'pricing' => [
                'price' => $course->getPrice(),
                'description' => $course->getPriceDescription(),
            ],
            'registration' => [
                'selfRegistration' => $course->getPublicRegistration(),
                'autoRegistration' => $course->getAutoRegistration(),
            ],
            'restrictions' => [
                'hidden' => $course->isHidden(),
            ],
            'tags' => $this->serializeTags($course),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $admin = $this->authorization->isGranted('ADMINISTRATE', $course);
            $serialized['permissions'] = [
                'open' => $admin || $this->authorization->isGranted('OPEN', $course),
                'edit' => $admin || $this->authorization->isGranted('EDIT', $course),
                'follow' => $admin || $this->authorization->isGranted('FOLLOW', $course),
                'administrate' => $admin || $this->authorization->isGranted('ADMINISTRATE', $course),
                'delete' => $admin || $this->authorization->isGranted('DELETE', $course),
            ];
        }

        if (!in_array(SerializerInterface::SERIALIZE_LIST, $options)) {
            $serialized = array_merge_recursive($serialized, [
                'display' => [
                    'hideSessions' => $course->getHideSessions(),
                ],
                'opening' => [
                    'session' => $course->getSessionOpening(),
                ],
                'participants' => $this->courseRepo->countParticipants($course),
                'registration' => [
                    'selfUnregistration' => $course->getPublicUnregistration(),
                    'validation' => $course->hasValidation(),
                    'userValidation' => $course->hasConfirmation(),
                    'mail' => $course->getRegistrationMail(),
                    'pendingRegistrations' => $course->getPendingRegistrations(),
                    'learnerRole' => $course->getLearnerRole() ?
                        $this->roleSerializer->serialize($course->getLearnerRole(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                        null,
                    'tutorRole' => $course->getTutorRole() ?
                        $this->roleSerializer->serialize($course->getTutorRole(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                        null,
                    'form' => array_map(function (PanelFacet $panelFacet) {
                        return $this->panelFacetSerializer->serialize($panelFacet);
                    }, $course->getPanelFacets()->toArray()),
                ],
                'organizations' => array_map(function (Organization $organization) {
                    return $this->orgaSerializer->serialize($organization, [SerializerInterface::SERIALIZE_MINIMAL]);
                }, $course->getOrganizations()->toArray()),
                'workspace' => $course->getWorkspace() ?
                    $this->workspaceSerializer->serialize($course->getWorkspace(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
            ]);
        }

        return $serialized;
    }

    public function deserialize(array $data, Course $course, array $options): Course
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $course);
        } else {
            $course->refreshUuid();
        }

        $this->sipe('code', 'setCode', $data, $course);
        $this->sipe('name', 'setName', $data, $course);
        $this->sipe('description', 'setDescription', $data, $course);
        $this->sipe('plainDescription', 'setPlainDescription', $data, $course);
        $this->sipe('poster', 'setPoster', $data, $course);

        $this->sipe('display.hideSessions', 'setHideSessions', $data, $course);

        $this->sipe('restrictions.hidden', 'setHidden', $data, $course);

        $this->sipe('opening.session', 'setSessionOpening', $data, $course);

        $this->sipe('certification', 'setCertification', $data, $course);
        $this->sipe('objectives', 'setObjectives', $data, $course);
        $this->sipe('requirements', 'setRequirements', $data, $course);
        $this->sipe('targetAudience', 'setTargetAudience', $data, $course);
        $this->sipe('pricing.price', 'setPrice', $data, $course);
        $this->sipe('pricing.description', 'setPriceDescription', $data, $course);

        if (isset($data['meta'])) {
            $this->sipe('meta.duration', 'setDefaultSessionDuration', $data, $course);

            if (isset($data['meta']['created'])) {
                $course->setCreatedAt(DateNormalizer::denormalize($data['meta']['created']));
            }

            if (isset($data['meta']['updated'])) {
                $course->setUpdatedAt(DateNormalizer::denormalize($data['meta']['updated']));
            }

            if (!empty($data['meta']['creator'])) {
                /** @var User $creator */
                $creator = $this->om->getObject($data['meta']['creator'], User::class);
                $course->setCreator($creator);
            }

            if (isset($data['meta']['public'])) {
                $course->setPublic($data['meta']['public']);
            }
        }

        if (isset($data['registration'])) {
            $this->sipe('registration.selfRegistration', 'setPublicRegistration', $data, $course);
            $this->sipe('registration.autoRegistration', 'setAutoRegistration', $data, $course);
            $this->sipe('registration.selfUnregistration', 'setPublicUnregistration', $data, $course);
            $this->sipe('registration.validation', 'setRegistrationValidation', $data, $course);
            $this->sipe('registration.userValidation', 'setUserValidation', $data, $course);
            $this->sipe('registration.mail', 'setRegistrationMail', $data, $course);
            $this->sipe('registration.pendingRegistrations', 'setPendingRegistrations', $data, $course);

            if (array_key_exists('learnerRole', $data['registration'])) {
                $learnerRole = null;
                if (!empty($data['registration']['learnerRole'])) {
                    $learnerRole = $this->om->getRepository(Role::class)->findOneBy(['uuid' => $data['registration']['learnerRole']['id']]);
                }

                $course->setLearnerRole($learnerRole);
            }

            if (array_key_exists('tutorRole', $data['registration'])) {
                $tutorRole = null;
                if (!empty($data['registration']['tutorRole'])) {
                    $tutorRole = $this->om->getRepository(Role::class)->findOneBy(['uuid' => $data['registration']['tutorRole']['id']]);
                }

                $course->setTutorRole($tutorRole);
            }

            if (array_key_exists('form', $data['registration'])) {
                $sectionIds = [];
                foreach ($data['registration']['form'] as $section) {
                    // check if the section exists first
                    $panelFacet = $this->om->getObject($section, PanelFacet::class) ?? new PanelFacet();
                    $this->panelFacetSerializer->deserialize($section, $panelFacet, $options);

                    $course->addPanelFacet($panelFacet);
                    $sectionIds[] = $panelFacet->getUuid();
                }

                foreach ($course->getPanelFacets() as $panelFacet) {
                    if (!in_array($panelFacet->getUuid(), $sectionIds)) {
                        $course->removePanelFacet($panelFacet);
                    }
                }
            }
        }

        if (array_key_exists('workspace', $data)) {
            $workspace = null;
            if (isset($data['workspace']['id'])) {
                /** @var Workspace $workspace */
                $workspace = $this->workspaceRepo->findOneBy(['uuid' => $data['workspace']['id']]);
            }
            $course->setWorkspace($workspace);
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

            $course->setOrganizations(array_values($organizations));
        }

        if (isset($data['tags'])) {
            $this->deserializeTags($course, $data['tags'], $options);
        }

        return $course;
    }

    /**
     * Serializes Course tags.
     * Forwards the tag serialization to ItemTagSerializer.
     */
    private function serializeTags(Course $course): array
    {
        $event = new GenericDataEvent([
            'class' => Course::class,
            'ids' => [$course->getUuid()],
        ]);
        $this->eventDispatcher->dispatch($event, 'claroline_retrieve_used_tags_by_class_and_ids');

        return $event->getResponse() ?? [];
    }

    /**
     * Deserializes Course tags.
     */
    private function deserializeTags(Course $course, array $tags = [], array $options = []): void
    {
        if (in_array(Options::PERSIST_TAG, $options)) {
            $event = new GenericDataEvent([
                'tags' => $tags,
                'data' => [
                    [
                        'class' => Course::class,
                        'id' => $course->getUuid(),
                        'name' => $course->getName(),
                    ],
                ],
                'replace' => true,
            ]);

            $this->eventDispatcher->dispatch($event, 'claroline_tag_multiple_data');
        }
    }
}
