<?php

namespace Claroline\CursusBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\RoleSerializer;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Facet\PanelFacetSerializer;
use Claroline\CoreBundle\API\Serializer\LocationSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\Location;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Repository\CourseRepository;
use Claroline\CursusBundle\Repository\SessionRepository;
use Claroline\TemplateBundle\Entity\Template;
use Claroline\TemplateBundle\Serializer\TemplateSerializer;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SessionSerializer
{
    use SerializerTrait;

    private CourseRepository $courseRepo;
    private SessionRepository $sessionRepo;
    private ObjectRepository $templateRepo;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly UserSerializer $userSerializer,
        private readonly RoleSerializer $roleSerializer,
        private readonly LocationSerializer $locationSerializer,
        private readonly WorkspaceSerializer $workspaceSerializer,
        private readonly TemplateSerializer $templateSerializer,
        private readonly PanelFacetSerializer $panelFacetSerializer,
        private readonly CourseSerializer $courseSerializer
    ) {
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
                'poster' => $session->getPoster(),
                'plainDescription' => $session->getPlainDescription(),
                'dates' => DateRangeNormalizer::normalize($session->getStartDate(), $session->getEndDate()),
            ];
        }

        $tutors = $this->om->getRepository(SessionUser::class)->findBy([
            'session' => $session,
            'type' => AbstractRegistration::TUTOR,
            'validated' => true,
            'confirmed' => true,
        ]);

        $serialized = [
            'autoId' => $session->getId(),
            'id' => $session->getUuid(),
            'code' => $session->getCode(),
            'name' => $session->getName(),
            'poster' => $session->getPoster(),
            'description' => $session->getDescription(),
            'plainDescription' => $session->getPlainDescription(),
            'dates' => DateRangeNormalizer::normalize($session->getStartDate(), $session->getEndDate()),
            'restrictions' => [
                'hidden' => $session->isHidden(),
                'users' => $session->getMaxUsers(),
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
                'default' => $session->isDefaultSession(),
                'canceled' => $session->isCanceled(),
                'cancelReason' => $session->getCancelReason(),
            ],
            'registration' => [
                'selfRegistration' => $session->getPublicRegistration(),
                'autoRegistration' => $session->getAutoRegistration(),
                'validation' => $session->hasValidation(),
                'userValidation' => $session->hasConfirmation(),
            ],
            'pricing' => [
                'price' => $session->getPrice(),
                'description' => $session->getPriceDescription(),
            ],
            'participants' => $this->sessionRepo->countParticipants($session),
            'tutors' => array_map(function (SessionUser $sessionUser) {
                return $this->userSerializer->serialize($sessionUser->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $tutors),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $serialized['permissions'] = [
                'open' => $this->authorization->isGranted('OPEN', $session),
                'edit' => $this->authorization->isGranted('EDIT', $session),
                'administrate' => $this->authorization->isGranted('ADMINISTRATE', $session),
                'follow' => $this->authorization->isGranted('FOLLOW', $session),
            ];
        }

        if (!in_array(SerializerInterface::SERIALIZE_LIST, $options)) {
            $serialized = array_merge_recursive($serialized, [
                'course' => $this->courseSerializer->serialize($session->getCourse(), [SerializerInterface::SERIALIZE_MINIMAL]),
                'registration' => [
                    'selfUnregistration' => $session->getPublicUnregistration(),
                    'mail' => $session->getRegistrationMail(),
                    'pendingRegistrations' => $session->getPendingRegistrations(),
                    'eventRegistrationType' => $session->getEventRegistrationType(),
                    'learnerRole' => $session->getLearnerRole() ?
                        $this->roleSerializer->serialize($session->getLearnerRole(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                        null,
                    'tutorRole' => $session->getTutorRole() ?
                        $this->roleSerializer->serialize($session->getTutorRole(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                        null,
                    'form' => array_map(function (PanelFacet $panelFacet) {
                        return $this->panelFacetSerializer->serialize($panelFacet);
                    }, $session->getCourse()->getPanelFacets()->toArray()),
                ],
                'invitationTemplate' => $session->getInvitationTemplate() ?
                    $this->templateSerializer->serialize($session->getInvitationTemplate(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
                'canceledTemplate' => $session->getCanceledTemplate() ?
                    $this->templateSerializer->serialize($session->getCanceledTemplate(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
            ]);
        }

        return $serialized;
    }

    public function deserialize(array $data, Session $session, array $options): Session
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $session);
        } else {
            $session->refreshUuid();
        }

        $this->sipe('code', 'setCode', $data, $session);
        $this->sipe('description', 'setDescription', $data, $session);

        if (isset($data['dates'])) {
            $dates = DateRangeNormalizer::denormalize($data['dates']);

            $session->setStartDate($dates[0]);
            $session->setEndDate($dates[1]);
        }

        if (isset($data['registration'])) {
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

        $template = null;
        if (!empty($data['invitationTemplate']) && $data['invitationTemplate']['id']) {
            $template = $this->templateRepo->findOneBy(['uuid' => $data['invitationTemplate']['id']]);
        }
        $session->setInvitationTemplate($template);

        $cancelTemplate = null;
        if (!empty($data['canceledTemplate']) && $data['canceledTemplate']['id']) {
            $cancelTemplate = $this->templateRepo->findOneBy(['uuid' => $data['canceledTemplate']['id']]);
        }
        $session->setCanceledTemplate($cancelTemplate);

        return $session;
    }
}
