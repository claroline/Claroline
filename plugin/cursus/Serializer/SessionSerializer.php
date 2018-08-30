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
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\SessionEvent;
use Claroline\CursusBundle\Manager\CursusManager;
use Claroline\CursusBundle\Repository\CourseRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.cursus.session")
 * @DI\Tag("claroline.serializer")
 */
class SessionSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var CursusManager */
    private $cursusManager;
    /** @var SerializerProvider */
    private $serializer;

    /** @var CourseRepository */
    private $courseRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;

    /**
     * SessionSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "cursusManager" = @DI\Inject("claroline.manager.cursus_manager"),
     *     "serializer"    = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param ObjectManager      $om
     * @param CursusManager      $cursusManager
     * @param SerializerProvider $serializer
     */
    public function __construct(
        ObjectManager $om,
        CursusManager $cursusManager,
        SerializerProvider $serializer
    ) {
        $this->om = $om;
        $this->cursusManager = $cursusManager;
        $this->serializer = $serializer;

        $this->courseRepo = $om->getRepository('Claroline\CursusBundle\Entity\Course');
        $this->workspaceRepo = $om->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace');
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/cursus/session.json';
    }

    /**
     * @param CourseSession $session
     * @param array         $options
     *
     * @return array
     */
    public function serialize(CourseSession $session, array $options = [])
    {
        $serialized = [
            'id' => $session->getUuid(),
            'name' => $session->getName(),
            'description' => $session->getDescription(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'meta' => [
                    'type' => $session->getType(),
                    'course' => $this->serializer->serialize($session->getCourse(), [Options::SERIALIZE_MINIMAL]),
                    'workspace' => $session->getWorkspace() ?
                        $this->serializer->serialize($session->getWorkspace(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'learnerRole' => $session->getLearnerRole() ?
                        $this->serializer->serialize($session->getLearnerRole(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'tutorRole' => $session->getTutorRole() ?
                        $this->serializer->serialize($session->getTutorRole(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'sessionStatus' => $session->getSessionStatus(),
                    'defaultSession' => $session->isDefaultSession(),
                    'creationDate' => DateNormalizer::normalize($session->getCreationDate()),
                    'order' => $session->getDisplayOrder(),
                    'color' => $session->getColor(),
                    'total' => $session->getTotal(),
                    'certificated' => $session->getCertificated(),
                ],
                'restrictions' => [
                    'maxUsers' => $session->getMaxUsers(),
                    'dates' => [
                        $session->getStartDate() ? DateNormalizer::normalize($session->getStartDate()) : null,
                        $session->getEndDate() ? DateNormalizer::normalize($session->getEndDate()) : null,
                    ],
                ],
                'registration' => [
                    'publicRegistration' => $session->getPublicRegistration(),
                    'publicUnregistration' => $session->getPublicUnregistration(),
                    'registrationValidation' => $session->getRegistrationValidation(),
                    'userValidation' => $session->getUserValidation(),
                    'organizationValidation' => $session->getOrganizationValidation(),
                    'eventRegistrationType' => $session->getEventRegistrationType(),
                ],
            ]);
        }

        return $serialized;
    }

    /**
     * @param array         $data
     * @param CourseSession $session
     *
     * @return CourseSession
     */
    public function deserialize($data, CourseSession $session)
    {
        $this->sipe('id', 'setUuid', $data, $session);
        $this->sipe('name', 'setName', $data, $session);
        $this->sipe('description', 'setDescription', $data, $session);

        $this->sipe('meta.type', 'setType', $data, $session);
        $this->sipe('meta.sessionStatus', 'setSessionStatus', $data, $session);
        $this->sipe('meta.defaultSession', 'setDefaultSession', $data, $session);
        $this->sipe('meta.order', 'setDisplayOrder', $data, $session);
        $this->sipe('meta.color', 'setColor', $data, $session);
        $this->sipe('meta.total', 'setTotal', $data, $session);
        $this->sipe('meta.certificated', 'setCertificated', $data, $session);

        $this->sipe('restrictions.maxUsers', 'setMaxUsers', $data, $session);

        $this->sipe('registration.publicRegistration', 'setPublicRegistration', $data, $session);
        $this->sipe('registration.publicUnregistration', 'setPublicUnregistration', $data, $session);
        $this->sipe('registration.registrationValidation', 'setRegistrationValidation', $data, $session);
        $this->sipe('registration.userValidation', 'setUserValidation', $data, $session);
        $this->sipe('registration.organizationValidation', 'setOrganizationValidation', $data, $session);
        $this->sipe('registration.eventRegistrationType', 'setEventRegistrationType', $data, $session);

        $startDate = isset($data['restrictions']['dates'][0]) ?
            DateNormalizer::denormalize($data['restrictions']['dates'][0]) :
            null;
        $endDate = isset($data['restrictions']['dates'][1]) ?
            DateNormalizer::denormalize($data['restrictions']['dates'][1]) :
            null;
        $session->setStartDate($startDate);
        $session->setEndDate($endDate);

        $course = $session->getCourse();
        // Sets course at creation
        if (empty($course) && isset($data['meta']['course']['id'])) {
            $course = $this->courseRepo->findOneBy(['uuid' => $data['meta']['course']['id']]);

            if ($course) {
                $session->setCourse($course);
            }
            // Creates default session event
            if ($course->getWithSessionEvent()) {
                $eventData = [
                    'name' => $session->getName(),
                    'meta' => [
                        'type' => SessionEvent::TYPE_NONE,
                    ],
                    'restrictions' => [
                        'dates' => [
                            $session->getStartDate() ? DateNormalizer::normalize($session->getStartDate()) : null,
                            $session->getEndDate() ? DateNormalizer::normalize($session->getEndDate()) : null,
                        ],
                    ],
                    'registration' => [
                        'registrationType' => $session->getEventRegistrationType(),
                    ],
                ];
                $event = $this->serializer->deserialize('Claroline\CursusBundle\Entity\SessionEvent', $eventData);
                $event->setSession($session);
                $this->om->persist($event);
            }
        }
        // Removes default session flag on all other sessions if this one is the default one
        if ($session->isDefaultSession()) {
            $this->cursusManager->resetDefaultSessionByCourse($course, $session);
        }

        $workspace = $session->getWorkspace();
        // Creates workspace, roles and default session event at creation
        if (empty($workspace) && !empty($course)) {
            $workspace = $course->getWorkspace();

            if (empty($workspace)) {
                $workspace = $this->cursusManager->generateWorkspace($session);
            }
            $session->setWorkspace($workspace);

            $learnerRole = $this->cursusManager->generateRoleForSession(
                $workspace,
                $course->getLearnerRoleName(),
                'learner'
            );
            $session->setLearnerRole($learnerRole);

            $tutorRole = $this->cursusManager->generateRoleForSession(
                $workspace,
                $course->getTutorRoleName(),
                'manager'
            );
            $session->setTutorRole($tutorRole);
        }

        return $session;
    }
}
