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
use Claroline\CoreBundle\API\Serializer\Location\LocationSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Location\Location;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Repository\SessionRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SessionSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var PublicFileSerializer */
    private $fileSerializer;
    /** @var UserSerializer */
    private $userSerializer;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var LocationSerializer */
    private $locationSerializer;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;
    /** @var ResourceNodeSerializer */
    private $resourceSerializer;
    /** @var CourseSerializer */
    private $courseSerializer;

    private $courseRepo;
    /** @var SessionRepository */
    private $sessionRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        PublicFileSerializer $fileSerializer,
        UserSerializer $userSerializer,
        RoleSerializer $roleSerializer,
        LocationSerializer $locationSerializer,
        WorkspaceSerializer $workspaceSerializer,
        ResourceNodeSerializer $resourceSerializer,
        CourseSerializer $courseSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
        $this->userSerializer = $userSerializer;
        $this->roleSerializer = $roleSerializer;
        $this->locationSerializer = $locationSerializer;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->resourceSerializer = $resourceSerializer;
        $this->courseSerializer = $courseSerializer;

        $this->courseRepo = $om->getRepository(Course::class);
        $this->sessionRepo = $om->getRepository(Session::class);
    }

    public function getSchema()
    {
        return '#/plugin/cursus/session.json';
    }

    public function serialize(Session $session, array $options = []): array
    {
        $serialized = [
            'id' => $session->getUuid(),
            'code' => $session->getCode(),
            'name' => $session->getName(),
            'description' => $session->getDescription(),
            'plainDescription' => $session->getPlainDescription(),
            'poster' => $this->serializePoster($session),
            'thumbnail' => $this->serializeThumbnail($session),
            'permissions' => [
                'open' => $this->authorization->isGranted('OPEN', $session),
                'edit' => $this->authorization->isGranted('EDIT', $session),
                'delete' => $this->authorization->isGranted('DELETE', $session),
                'register' => $this->authorization->isGranted('REGISTER', $session),
            ],
            'course' => $this->courseSerializer->serialize($session->getCourse(), [Options::SERIALIZE_MINIMAL]),
            'restrictions' => [
                'hidden' => $session->isHidden(),
                'users' => $session->getMaxUsers(),
                'dates' => DateRangeNormalizer::normalize($session->getStartDate(), $session->getEndDate()),
            ],
            'workspace' => $session->getWorkspace() ?
                $this->workspaceSerializer->serialize($session->getWorkspace(), [Options::SERIALIZE_MINIMAL]) :
                null,
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $tutors = $this->om->getRepository(SessionUser::class)->findBy([
                'session' => $session,
                'type' => AbstractRegistration::TUTOR,
                'validated' => true,
                'confirmed' => true,
            ]);

            $serialized = array_merge($serialized, [
                'location' => $session->getLocation() ?
                    $this->locationSerializer->serialize($session->getLocation(), [Options::SERIALIZE_MINIMAL]) :
                    null,
                'meta' => [
                    'creator' => $session->getCreator() ?
                        $this->userSerializer->serialize($session->getCreator(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'created' => DateNormalizer::normalize($session->getCreatedAt()),
                    'updated' => DateNormalizer::normalize($session->getUpdatedAt()),
                    'duration' => $session->getCourse() ? $session->getCourse()->getDefaultSessionDuration() : null,
                    'default' => $session->isDefaultSession(),
                    'order' => $session->getOrder(),
                    'learnerRole' => $session->getLearnerRole() ?
                        $this->roleSerializer->serialize($session->getLearnerRole(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'tutorRole' => $session->getTutorRole() ?
                        $this->roleSerializer->serialize($session->getTutorRole(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                ],
                'registration' => [
                    'selfRegistration' => $session->getPublicRegistration(),
                    'autoRegistration' => $session->getAutoRegistration(),
                    'selfUnregistration' => $session->getPublicUnregistration(),
                    'validation' => $session->getRegistrationValidation(),
                    'userValidation' => $session->getUserValidation(),
                    'mail' => $session->getRegistrationMail(),
                    'eventRegistrationType' => $session->getEventRegistrationType(),
                ],
                'pricing' => [
                    'price' => $session->getPrice(),
                    'description' => $session->getPriceDescription(),
                ],
                'participants' => $this->sessionRepo->countParticipants($session),
                'tutors' => array_map(function (SessionUser $sessionUser) {
                    return $this->userSerializer->serialize($sessionUser->getUser(), [Options::SERIALIZE_MINIMAL]);
                }, $tutors),
                'resources' => array_map(function (ResourceNode $resource) {
                    return $this->resourceSerializer->serialize($resource, [Options::SERIALIZE_MINIMAL]);
                }, $session->getResources()->toArray()),
            ]);
        }

        return $serialized;
    }

    public function deserialize(array $data, Session $session): Session
    {
        $this->sipe('id', 'setUuid', $data, $session);
        $this->sipe('code', 'setCode', $data, $session);
        $this->sipe('name', 'setName', $data, $session);
        $this->sipe('description', 'setDescription', $data, $session);
        $this->sipe('plainDescription', 'setPlainDescription', $data, $session);

        $this->sipe('meta.default', 'setDefaultSession', $data, $session);
        $this->sipe('meta.order', 'setOrder', $data, $session);

        $this->sipe('restrictions.users', 'setMaxUsers', $data, $session);
        $this->sipe('restrictions.hidden', 'setHidden', $data, $session);

        $this->sipe('registration.selfRegistration', 'setPublicRegistration', $data, $session);
        $this->sipe('registration.autoRegistration', 'setAutoRegistration', $data, $session);
        $this->sipe('registration.selfUnregistration', 'setPublicUnregistration', $data, $session);
        $this->sipe('registration.validation', 'setRegistrationValidation', $data, $session);
        $this->sipe('registration.userValidation', 'setUserValidation', $data, $session);
        $this->sipe('registration.mail', 'setRegistrationMail', $data, $session);
        $this->sipe('registration.eventRegistrationType', 'setEventRegistrationType', $data, $session);

        $this->sipe('pricing.price', 'setPrice', $data, $session);
        $this->sipe('pricing.description', 'setPriceDescription', $data, $session);

        if (isset($data['meta'])) {
            if (isset($data['meta']['created'])) {
                $session->setCreatedAt(DateNormalizer::denormalize($data['meta']['created']));
            }

            if (isset($data['meta']['updated'])) {
                $session->setUpdatedAt(DateNormalizer::denormalize($data['meta']['updated']));
            }

            if (!empty($data['meta']['creator'])) {
                /** @var User $creator */
                $creator = $this->om->getObject($data['meta']['creator'], User::class);
                $session->setCreator($creator);
            }
        }

        if (isset($data['restrictions']['dates'])) {
            $dates = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

            $session->setStartDate($dates[0]);
            $session->setEndDate($dates[1]);
        }

        if (isset($data['poster'])) {
            $session->setPoster($data['poster']['url'] ?? null);
        }

        if (isset($data['thumbnail'])) {
            $session->setThumbnail($data['thumbnail']['url'] ?? null);
        }

        $course = $session->getCourse();
        // Sets course at creation
        if (empty($course) && isset($data['course']['id'])) {
            /** @var Course $course */
            $course = $this->courseRepo->findOneBy(['uuid' => $data['course']['id']]);
            if ($course) {
                $session->setCourse($course);
            }
        }

        if (isset($data['location'])) {
            $location = null;
            if (!empty($data['location']['id'])) {
                $location = $this->om->getRepository(Location::class)->findOneBy(['uuid' => $data['location']['id']]);
            }

            $session->setLocation($location);
        }

        if (isset($data['resources'])) {
            $resources = [];
            foreach ($data['resources'] as $resourceData) {
                $resources[] = $this->om->getRepository(ResourceNode::class)->findOneBy(['uuid' => $resourceData['id']]);
            }

            $session->setResources($resources);
        }

        return $session;
    }

    private function serializePoster(Session $session)
    {
        if (!empty($session->getPoster())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $session->getPoster()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    private function serializeThumbnail(Session $session)
    {
        if (!empty($session->getThumbnail())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $session->getThumbnail()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }
}
