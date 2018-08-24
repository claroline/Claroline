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
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CursusBundle\Entity\CourseSession;
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
    /** @var CourseSerializer */
    private $courseSerializer;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;

    /** @var CourseRepository */
    private $courseRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;

    /**
     * SessionSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "courseSerializer"    = @DI\Inject("claroline.serializer.cursus.course"),
     *     "roleSerializer"      = @DI\Inject("claroline.serializer.role"),
     *     "workspaceSerializer" = @DI\Inject("claroline.serializer.workspace")
     * })
     *
     * @param ObjectManager       $om
     * @param CourseSerializer    $courseSerializer
     * @param RoleSerializer      $roleSerializer
     * @param WorkspaceSerializer $workspaceSerializer
     */
    public function __construct(
        ObjectManager $om,
        CourseSerializer $courseSerializer,
        RoleSerializer $roleSerializer,
        WorkspaceSerializer $workspaceSerializer
    ) {
        $this->om = $om;
        $this->courseSerializer = $courseSerializer;
        $this->roleSerializer = $roleSerializer;
        $this->workspaceSerializer = $workspaceSerializer;

        $this->courseRepo = $om->getRepository('Claroline\CursusBundle\Entity\Course');
        $this->workspaceRepo = $om->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace');
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
                    'course' => $this->courseSerializer->serialize($session->getCourse(), [Options::SERIALIZE_MINIMAL]),
                    'workspace' => $session->getWorkspace() ?
                        $this->workspaceSerializer->serialize($session->getWorkspace()) :
                        null,
                    'learnerRole' => $session->getLearnerRole() ?
                        $this->roleSerializer->serialize($session->getLearnerRole(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'tutorRole' => $session->getTutorRole() ?
                        $this->roleSerializer->serialize($session->getTutorRole(), [Options::SERIALIZE_MINIMAL]) :
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
        $this->sipe('meta.creationDate', 'setCreationDate', $data, $session);
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

        // TODO: meta.learnerRole && meta.tutorRole

        if (isset($data['meta']['workspace']['uuid'])) {
            $workspace = $this->workspaceRepo->findOneBy(['uuid' => $data['workspace']['uuid']]);

            if ($workspace) {
                $session->setWorkspace($workspace);
            }
        }
        $course = $session->getCourse();

        if (empty($course) && isset($data['meta']['course']['id'])) {
            $course = $this->courseRepo->findOneBy(['uuid' => $data['meta']['course']['id']]);

            if ($course) {
                $session->setCourse($course);
            }
        }

        return $session;
    }
}
