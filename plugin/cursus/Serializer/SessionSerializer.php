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
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseSession;

class SessionSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var PublicFileSerializer */
    private $fileSerializer;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;
    /** @var CourseSerializer */
    private $courseSerializer;

    private $courseRepo;

    /**
     * SessionSerializer constructor.
     *
     * @param ObjectManager        $om
     * @param PublicFileSerializer $fileSerializer
     * @param RoleSerializer       $roleSerializer
     * @param WorkspaceSerializer  $workspaceSerializer
     * @param CourseSerializer     $courseSerializer
     */
    public function __construct(
        ObjectManager $om,
        PublicFileSerializer $fileSerializer,
        RoleSerializer $roleSerializer,
        WorkspaceSerializer $workspaceSerializer,
        CourseSerializer $courseSerializer
    ) {
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
        $this->roleSerializer = $roleSerializer;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->courseSerializer = $courseSerializer;

        $this->courseRepo = $om->getRepository(Course::class);
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
            'code' => $session->getCode(),
            'name' => $session->getName(),
            'description' => $session->getDescription(),
            'poster' => $this->serializePoster($session),
            'thumbnail' => $this->serializeThumbnail($session),
            'workspace' => $session->getWorkspace() ?
                $this->workspaceSerializer->serialize($session->getWorkspace(), [Options::SERIALIZE_MINIMAL]) :
                null,
            'restrictions' => [
                'users' => $session->getMaxUsers(),
                'dates' => DateRangeNormalizer::normalize($session->getStartDate(), $session->getEndDate()),
            ],
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'meta' => [
                    'default' => $session->isDefaultSession(),
                    'order' => $session->getDisplayOrder(),

                    'type' => $session->getType(),
                    'course' => $this->courseSerializer->serialize($session->getCourse(), [Options::SERIALIZE_MINIMAL]),
                    'learnerRole' => $session->getLearnerRole() ?
                        $this->roleSerializer->serialize($session->getLearnerRole(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'tutorRole' => $session->getTutorRole() ?
                        $this->roleSerializer->serialize($session->getTutorRole(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'sessionStatus' => $session->getSessionStatus(),
                    'creationDate' => DateNormalizer::normalize($session->getCreationDate()),
                    'color' => $session->getColor(),
                    //'total' => $session->getTotal(),
                    'certificated' => $session->getCertificated(),
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
    public function deserialize(array $data, CourseSession $session)
    {
        $this->sipe('id', 'setUuid', $data, $session);
        $this->sipe('code', 'setCode', $data, $session);
        $this->sipe('name', 'setName', $data, $session);
        $this->sipe('description', 'setDescription', $data, $session);

        $this->sipe('meta.default', 'setDefaultSession', $data, $session);
        $this->sipe('meta.type', 'setType', $data, $session);
        $this->sipe('meta.sessionStatus', 'setSessionStatus', $data, $session);
        $this->sipe('meta.order', 'setDisplayOrder', $data, $session);
        $this->sipe('meta.color', 'setColor', $data, $session);
        //$this->sipe('meta.total', 'setTotal', $data, $session);
        $this->sipe('meta.certificated', 'setCertificated', $data, $session);

        $this->sipe('restrictions.users', 'setMaxUsers', $data, $session);

        $this->sipe('registration.publicRegistration', 'setPublicRegistration', $data, $session);
        $this->sipe('registration.publicUnregistration', 'setPublicUnregistration', $data, $session);
        $this->sipe('registration.registrationValidation', 'setRegistrationValidation', $data, $session);
        $this->sipe('registration.userValidation', 'setUserValidation', $data, $session);
        $this->sipe('registration.organizationValidation', 'setOrganizationValidation', $data, $session);
        $this->sipe('registration.eventRegistrationType', 'setEventRegistrationType', $data, $session);

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
        if (empty($course) && isset($data['meta']['course']['id'])) {
            /** @var Course $course */
            $course = $this->courseRepo->findOneBy(['uuid' => $data['meta']['course']['id']]);
            if ($course) {
                $session->setCourse($course);
            }
        }

        return $session;
    }

    private function serializePoster(CourseSession $session)
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

    private function serializeThumbnail(CourseSession $session)
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
