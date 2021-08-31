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
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\User\OrganizationSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CursusBundle\Entity\Course;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CourseSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var PublicFileSerializer */
    private $fileSerializer;
    /** @var UserSerializer */
    private $userSerializer;
    /** @var OrganizationSerializer */
    private $orgaSerializer;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;

    private $orgaRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;
    private $courseRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        ObjectManager $om,
        PublicFileSerializer $fileSerializer,
        UserSerializer $userSerializer,
        OrganizationSerializer $orgaSerializer,
        WorkspaceSerializer $workspaceSerializer
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
        $this->userSerializer = $userSerializer;
        $this->orgaSerializer = $orgaSerializer;
        $this->workspaceSerializer = $workspaceSerializer;

        $this->orgaRepo = $om->getRepository(Organization::class);
        $this->workspaceRepo = $om->getRepository(Workspace::class);
        $this->courseRepo = $om->getRepository(Course::class);
    }

    public function getSchema()
    {
        return '#/plugin/cursus/course.json';
    }

    public function serialize(Course $course, array $options = []): array
    {
        $serialized = [
            'id' => $course->getUuid(),
            'code' => $course->getCode(),
            'name' => $course->getName(),
            'slug' => $course->getSlug(),
            'description' => $course->getDescription(),
            'plainDescription' => $course->getPlainDescription(),
            'thumbnail' => $this->serializeThumbnail($course),
            'permissions' => [
                'open' => $this->authorization->isGranted('OPEN', $course),
                'edit' => $this->authorization->isGranted('EDIT', $course),
                'delete' => $this->authorization->isGranted('DELETE', $course),
                'register' => $this->authorization->isGranted('REGISTER', $course),
            ],
            'opening' => [
                'session' => $course->getSessionOpening(),
            ],
            'tags' => $this->serializeTags($course),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'poster' => $this->serializePoster($course),
                'parent' => $course->getParent() ? $this->serialize($course->getParent(), [Options::SERIALIZE_MINIMAL]) : null,
                'meta' => [
                    'creator' => $course->getCreator() ?
                        $this->userSerializer->serialize($course->getCreator(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'created' => DateNormalizer::normalize($course->getCreatedAt()),
                    'updated' => DateNormalizer::normalize($course->getUpdatedAt()),
                    'tutorRoleName' => $course->getTutorRoleName(),
                    'learnerRoleName' => $course->getLearnerRoleName(),
                    'duration' => $course->getDefaultSessionDuration(),
                    'order' => $course->getOrder(),
                ],
                'restrictions' => [
                    'hidden' => $course->isHidden(),
                    'active' => $course->hasAvailableSession(),
                    'users' => $course->getMaxUsers(),
                ],
                'registration' => [
                    'propagate' => $course->getPropagateRegistration(),
                    'selfRegistration' => $course->getPublicRegistration(),
                    'autoRegistration' => $course->getAutoRegistration(),
                    'selfUnregistration' => $course->getPublicUnregistration(),
                    'validation' => $course->getRegistrationValidation(),
                    'userValidation' => $course->getUserValidation(),
                    'mail' => $course->getRegistrationMail(),
                    'pendingRegistrations' => $course->getPendingRegistrations(),
                ],
                'pricing' => [
                    'price' => $course->getPrice(),
                    'description' => $course->getPriceDescription(),
                ],
                'workspace' => $course->getWorkspace() ?
                    $this->workspaceSerializer->serialize($course->getWorkspace(), [Options::SERIALIZE_MINIMAL]) :
                    null,
                'workspaceModel' => $course->getWorkspaceModel() ?
                    $this->workspaceSerializer->serialize($course->getWorkspaceModel(), [Options::SERIALIZE_MINIMAL]) :
                    null,
                'organizations' => array_map(function (Organization $organization) {
                    return $this->orgaSerializer->serialize($organization, [Options::SERIALIZE_MINIMAL]);
                }, $course->getOrganizations()->toArray()),
                'children' => array_map(function (Course $child) {
                    return $this->serialize($child, [Options::SERIALIZE_MINIMAL]);
                }, $course->getChildren()->toArray()),
            ]);
        }

        return $serialized;
    }

    public function deserialize(array $data, Course $course, array $options): Course
    {
        $this->sipe('id', 'setUuid', $data, $course);
        $this->sipe('code', 'setCode', $data, $course);
        $this->sipe('name', 'setName', $data, $course);
        $this->sipe('description', 'setDescription', $data, $course);
        $this->sipe('plainDescription', 'setPlainDescription', $data, $course);

        $this->sipe('meta.tutorRoleName', 'setTutorRoleName', $data, $course);
        $this->sipe('meta.learnerRoleName', 'setLearnerRoleName', $data, $course);
        $this->sipe('meta.icon', 'setIcon', $data, $course);
        $this->sipe('meta.duration', 'setDefaultSessionDuration', $data, $course);
        $this->sipe('meta.order', 'setOrder', $data, $course);

        $this->sipe('restrictions.users', 'setMaxUsers', $data, $course);
        $this->sipe('restrictions.hidden', 'setHidden', $data, $course);

        $this->sipe('registration.propagate', 'setPropagateRegistration', $data, $course);
        $this->sipe('registration.selfRegistration', 'setPublicRegistration', $data, $course);
        $this->sipe('registration.autoRegistration', 'setAutoRegistration', $data, $course);
        $this->sipe('registration.selfUnregistration', 'setPublicUnregistration', $data, $course);
        $this->sipe('registration.validation', 'setRegistrationValidation', $data, $course);
        $this->sipe('registration.userValidation', 'setUserValidation', $data, $course);
        $this->sipe('registration.mail', 'setRegistrationMail', $data, $course);
        $this->sipe('registration.pendingRegistrations', 'setPendingRegistrations', $data, $course);

        $this->sipe('opening.session', 'setSessionOpening', $data, $course);

        $this->sipe('pricing.price', 'setPrice', $data, $course);
        $this->sipe('pricing.description', 'setPriceDescription', $data, $course);

        if (isset($data['meta'])) {
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
        }

        if (isset($data['parent'])) {
            $parent = null;
            if (!empty($data['parent'])) {
                $parent = $this->courseRepo->findOneBy(['uuid' => $data['parent']['id']]);
            }

            $course->setParent($parent);
        }

        if (isset($data['poster'])) {
            $course->setPoster($data['poster']['url'] ?? null);
        }

        if (isset($data['thumbnail'])) {
            $course->setThumbnail($data['thumbnail']['url'] ?? null);
        }

        if (isset($data['workspace'])) {
            $workspace = null;
            if (isset($data['workspace']['id'])) {
                /** @var Workspace $workspace */
                $workspace = $this->workspaceRepo->findOneBy(['uuid' => $data['workspace']['id']]);
            }
            $course->setWorkspace($workspace);
        }

        if (isset($data['workspaceModel'])) {
            $workspace = null;
            if (isset($data['workspaceModel']['id'])) {
                /** @var Workspace $workspace */
                $workspace = $this->workspaceRepo->findOneBy(['uuid' => $data['workspaceModel']['id']]);
            }
            $course->setWorkspaceModel($workspace);
        }

        if (isset($data['organizations'])) {
            $course->emptyOrganizations();
            foreach ($data['organizations'] as $organizationData) {
                /** @var Organization $organization */
                $organization = $this->orgaRepo->findOneBy(['uuid' => $organizationData['id']]);
                if ($organization) {
                    $course->addOrganization($organization);
                }
            }
        }

        if (isset($data['tags'])) {
            $this->deserializeTags($course, $data['tags'], $options);
        }

        return $course;
    }

    private function serializePoster(Course $course)
    {
        if (!empty($course->getPoster())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $course->getPoster()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    private function serializeThumbnail(Course $course)
    {
        if (!empty($course->getThumbnail())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $course->getThumbnail()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
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
    private function deserializeTags(Course $course, array $tags = [], array $options = [])
    {
        if (in_array(Options::PERSIST_TAG, $options)) {
            $user = null;

            if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
                $user = $this->tokenStorage->getToken()->getUser();
            }

            $event = new GenericDataEvent([
                'user' => $user,
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
