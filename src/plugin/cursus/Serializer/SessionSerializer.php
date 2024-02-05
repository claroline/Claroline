<?php

namespace Claroline\CursusBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\RoleSerializer;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Location\LocationSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\Template\TemplateSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Location\Location;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Repository\CourseRepository;
use Claroline\CursusBundle\Repository\SessionRepository;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SessionSerializer
{
    use SerializerTrait;

    private AuthorizationCheckerInterface $authorization;
    private ObjectManager $om;
    private UserSerializer $userSerializer;
    private RoleSerializer $roleSerializer;
    private LocationSerializer $locationSerializer;
    private WorkspaceSerializer $workspaceSerializer;
    private ResourceNodeSerializer $resourceSerializer;
    private CourseSerializer $courseSerializer;
    private TemplateSerializer $templateSerializer;

    private CourseRepository $courseRepo;
    private SessionRepository $sessionRepo;
    private ObjectRepository $templateRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        UserSerializer $userSerializer,
        RoleSerializer $roleSerializer,
        LocationSerializer $locationSerializer,
        WorkspaceSerializer $workspaceSerializer,
        ResourceNodeSerializer $resourceSerializer,
        CourseSerializer $courseSerializer,
        TemplateSerializer $templateSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->userSerializer = $userSerializer;
        $this->roleSerializer = $roleSerializer;
        $this->locationSerializer = $locationSerializer;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->resourceSerializer = $resourceSerializer;
        $this->courseSerializer = $courseSerializer;
        $this->templateSerializer = $templateSerializer;

        $this->courseRepo = $om->getRepository(Course::class);
        $this->sessionRepo = $om->getRepository(Session::class);
        $this->templateRepo = $om->getRepository(Template::class);
    }

    public function getClass(): string
    {
        return Session::class;
    }

    public function getSchema(): string
    {
        return '#/plugin/cursus/session.json';
    }

    public function serialize(Session $session, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $session->getUuid(),
                'code' => $session->getCode(),
                'name' => $session->getName(),
                'thumbnail' => $session->getThumbnail(),
                'course' => $this->courseSerializer->serialize($session->getCourse(), [SerializerInterface::SERIALIZE_MINIMAL]), // it is required to generate the link to the session
                'restrictions' => [
                    'dates' => DateRangeNormalizer::normalize($session->getStartDate(), $session->getEndDate()),
                ],
            ];
        }

        $tutors = $this->om->getRepository(SessionUser::class)->findBy([
            'session' => $session,
            'type' => AbstractRegistration::TUTOR,
            'validated' => true,
            'confirmed' => true,
        ]);

        return [
            'autoId' => $session->getId(),
            'id' => $session->getUuid(),
            'code' => $session->getCode(),
            'name' => $session->getName(),
            'thumbnail' => $session->getThumbnail(),
            'poster' => $session->getPoster(),
            'description' => $session->getDescription(),
            'plainDescription' => $session->getPlainDescription(),
            'course' => $this->courseSerializer->serialize($session->getCourse(), [SerializerInterface::SERIALIZE_MINIMAL]), // it is required to generate the link to the session
            'permissions' => [
                'open' => $this->authorization->isGranted('OPEN', $session),
                'edit' => $this->authorization->isGranted('EDIT', $session),
                'delete' => $this->authorization->isGranted('DELETE', $session),
                'register' => $this->authorization->isGranted('REGISTER', $session),
            ],
            'restrictions' => [
                'hidden' => $session->isHidden(),
                'users' => $session->getMaxUsers(),
                'dates' => DateRangeNormalizer::normalize($session->getStartDate(), $session->getEndDate()),
            ],
            'workspace' => $session->getWorkspace() ?
                $this->workspaceSerializer->serialize($session->getWorkspace(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                null,
            'location' => $session->getLocation() ?
                $this->locationSerializer->serialize($session->getLocation(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                null,
            'meta' => [
                'creator' => $session->getCreator() ?
                    $this->userSerializer->serialize($session->getCreator(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
                'created' => DateNormalizer::normalize($session->getCreatedAt()),
                'updated' => DateNormalizer::normalize($session->getUpdatedAt()),
                'duration' => $session->getCourse() ? $session->getCourse()->getDefaultSessionDuration() : null,
                'default' => $session->isDefaultSession(),
            ],
            'display' => [
                'order' => $session->getOrder(),
            ],
            'registration' => [
                'selfRegistration' => $session->getPublicRegistration(),
                'autoRegistration' => $session->getAutoRegistration(),
                'selfUnregistration' => $session->getPublicUnregistration(),
                'validation' => $session->getRegistrationValidation(),
                'userValidation' => $session->getUserValidation(),
                'mail' => $session->getRegistrationMail(),
                'pendingRegistrations' => $session->getPendingRegistrations(),
                'eventRegistrationType' => $session->getEventRegistrationType(),
                'learnerRole' => $session->getLearnerRole() ?
                    $this->roleSerializer->serialize($session->getLearnerRole(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
                'tutorRole' => $session->getTutorRole() ?
                    $this->roleSerializer->serialize($session->getTutorRole(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
            ],
            'pricing' => [
                'price' => $session->getPrice(),
                'description' => $session->getPriceDescription(),
            ],
            'participants' => $this->sessionRepo->countParticipants($session),
            'tutors' => array_map(function (SessionUser $sessionUser) {
                return $this->userSerializer->serialize($sessionUser->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $tutors),
            'resources' => array_map(function (ResourceNode $resource) {
                return $this->resourceSerializer->serialize($resource, [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $session->getResources()->toArray()),
            'invitationTemplate' => $session->getInvitationTemplate() ?
                $this->templateSerializer->serialize($session->getInvitationTemplate(), [Options::SERIALIZE_MINIMAL]) :
                null,
        ];
    }

    public function deserialize(array $data, Session $session): Session
    {
        $this->sipe('id', 'setUuid', $data, $session);
        $this->sipe('code', 'setCode', $data, $session);
        $this->sipe('name', 'setName', $data, $session);
        $this->sipe('description', 'setDescription', $data, $session);
        $this->sipe('plainDescription', 'setPlainDescription', $data, $session);
        $this->sipe('poster', 'setPoster', $data, $session);
        $this->sipe('thumbnail', 'setThumbnail', $data, $session);

        $this->sipe('display.order', 'setOrder', $data, $session);

        if (isset($data['registration'])) {
            $this->sipe('registration.selfRegistration', 'setPublicRegistration', $data, $session);
            $this->sipe('registration.autoRegistration', 'setAutoRegistration', $data, $session);
            $this->sipe('registration.selfUnregistration', 'setPublicUnregistration', $data, $session);
            $this->sipe('registration.validation', 'setRegistrationValidation', $data, $session);
            $this->sipe('registration.userValidation', 'setUserValidation', $data, $session);
            $this->sipe('registration.mail', 'setRegistrationMail', $data, $session);
            $this->sipe('registration.pendingRegistrations', 'setPendingRegistrations', $data, $session);
            $this->sipe('registration.eventRegistrationType', 'setEventRegistrationType', $data, $session);

            if (array_key_exists('learnerRole', $data['registration'])) {
                $learnerRole = null;
                if (!empty($data['registration']['learnerRole'])) {
                    $learnerRole = $this->om->getRepository(Role::class)->findOneBy(['uuid' => $data['registration']['learnerRole']['id']]);
                }

                $session->setLearnerRole($learnerRole);
            }

            if (array_key_exists('tutorRole', $data['registration'])) {
                $tutorRole = null;
                if (!empty($data['registration']['tutorRole'])) {
                    $tutorRole = $this->om->getRepository(Role::class)->findOneBy(['uuid' => $data['registration']['tutorRole']['id']]);
                }

                $session->setTutorRole($tutorRole);
            }
        }

        $this->sipe('pricing.price', 'setPrice', $data, $session);
        $this->sipe('pricing.description', 'setPriceDescription', $data, $session);

        if (isset($data['meta'])) {
            $this->sipe('meta.default', 'setDefaultSession', $data, $session);

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

        if (isset($data['restrictions'])) {
            $this->sipe('restrictions.users', 'setMaxUsers', $data, $session);
            $this->sipe('restrictions.hidden', 'setHidden', $data, $session);

            if (isset($data['restrictions']['dates'])) {
                $dates = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

                $session->setStartDate($dates[0]);
                $session->setEndDate($dates[1]);
            }
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

        $template = null;
        if (!empty($data['invitationTemplate']) && $data['invitationTemplate']['id']) {
            $template = $this->templateRepo->findOneBy(['uuid' => $data['invitationTemplate']['id']]);
        }
        $session->setInvitationTemplate($template);

        return $session;
    }
}
