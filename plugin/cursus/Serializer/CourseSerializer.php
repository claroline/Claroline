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
use Claroline\CoreBundle\Repository\Organization\OrganizationRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CursusBundle\Entity\Course;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.serializer.cursus.course")
 * @DI\Tag("claroline.serializer")
 */
class CourseSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var OrganizationRepository */
    private $organizationRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;

    /**
     * CourseSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer"   = @DI\Inject("claroline.api.serializer"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param ObjectManager         $om
     * @param SerializerProvider    $serializer
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;

        $this->organizationRepo = $om->getRepository('Claroline\CoreBundle\Entity\Organization\Organization');
        $this->workspaceRepo = $om->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace');
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/cursus/course.json';
    }

    /**
     * @param Course $course
     * @param array  $options
     *
     * @return array
     */
    public function serialize(Course $course, array $options = [])
    {
        $serialized = [
            'id' => $course->getUuid(),
            'code' => $course->getCode(),
            'title' => $course->getTitle(),
            'description' => $course->getDescription(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'meta' => [
                    'workspaceModel' => $course->getWorkspaceModel() ?
                        $this->serializer->serialize($course->getWorkspaceModel(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'workspace' => $course->getWorkspace() ?
                        $this->serializer->serialize($course->getWorkspace(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                    'tutorRoleName' => $course->getTutorRoleName(),
                    'learnerRoleName' => $course->getLearnerRoleName(),
                    'icon' => $course->getIcon(),
                    'defaultSessionDuration' => $course->getDefaultSessionDuration(),
                    'withSessionEvent' => $course->getWithSessionEvent(),
                    'order' => $course->getDisplayOrder(),
                ],
                'restrictions' => [
                    'maxUsers' => $course->getMaxUsers(),
                ],
                'registration' => [
                    'publicRegistration' => $course->getPublicRegistration(),
                    'publicUnregistration' => $course->getPublicUnregistration(),
                    'registrationValidation' => $course->getRegistrationValidation(),
                    'userValidation' => $course->getUserValidation(),
                    'organizationValidation' => $course->getOrganizationValidation(),
                ],
            ]);
        }

        return $serialized;
    }

    /**
     * @param array  $data
     * @param Course $course
     *
     * @return Course
     */
    public function deserialize($data, Course $course)
    {
        $this->sipe('id', 'setUuid', $data, $course);
        $this->sipe('code', 'setCode', $data, $course);
        $this->sipe('title', 'setTitle', $data, $course);
        $this->sipe('description', 'setDescription', $data, $course);

        $this->sipe('meta.tutorRoleName', 'setTutorRoleName', $data, $course);
        $this->sipe('meta.learnerRoleName', 'setLearnerRoleName', $data, $course);
        $this->sipe('meta.icon', 'setIcon', $data, $course);
        $this->sipe('meta.defaultSessionDuration', 'setDefaultSessionDuration', $data, $course);
        $this->sipe('meta.withSessionEvent', 'setWithSessionEvent', $data, $course);
        $this->sipe('meta.order', 'setDisplayOrder', $data, $course);

        $this->sipe('restrictions.maxUsers', 'setMaxUsers', $data, $course);

        $this->sipe('registration.publicRegistration', 'setPublicRegistration', $data, $course);
        $this->sipe('registration.publicUnregistration', 'setPublicUnregistration', $data, $course);
        $this->sipe('registration.registrationValidation', 'setRegistrationValidation', $data, $course);
        $this->sipe('registration.userValidation', 'setUserValidation', $data, $course);
        $this->sipe('registration.organizationValidation', 'setOrganizationValidation', $data, $course);

        $workspace = isset($data['meta']['workspace']['uuid']) ?
            $this->workspaceRepo->findOneBy(['uuid' => $data['meta']['workspace']['uuid']]) :
            null;
        $course->setWorkspace($workspace);

        $workspaceModel = isset($data['meta']['workspaceModel']['uuid']) ?
            $this->workspaceRepo->findOneBy(['uuid' => $data['meta']['workspaceModel']['uuid']]) :
            null;
        $course->setWorkspaceModel($workspaceModel);

        $organizations = $course->getOrganizations()->toArray();

        // If Course is associated to no organization, initializes it with organizations administrated by authenticated user
        // or at last resort with default organizations
        if (0 === count($organizations)) {
            $user = $this->tokenStorage->getToken()->getUser();
            $useDefaultOrganizations = false;

            if ('anon.' !== $user) {
                $userOrganizations = $user->getAdministratedOrganizations()->toArray();

                if (0 < count($userOrganizations)) {
                    foreach ($userOrganizations as $organization) {
                        $course->addOrganization($organization);
                    }
                } else {
                    $useDefaultOrganizations = true;
                }
            } else {
                $useDefaultOrganizations = true;
            }
            // Initializes Course with default organizations if no others organization is found
            if ($useDefaultOrganizations) {
                $defaultOrganizations = $this->organizationRepo->findBy(['default' => true]);

                foreach ($defaultOrganizations as $organization) {
                    $course->addOrganization($organization);
                }
            }
        }

        return $course;
    }
}
