<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller\API;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\Organization\LocationManager;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionGroup;
use Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSessionUser;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\DocumentModel;
use Claroline\CursusBundle\Entity\SessionEvent;
use Claroline\CursusBundle\Entity\SessionEventUser;
use Claroline\CursusBundle\Event\Log\LogCourseEditEvent;
use Claroline\CursusBundle\Event\Log\LogCourseSessionEditEvent;
use Claroline\CursusBundle\Event\Log\LogCursusEditEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventEditEvent;
use Claroline\CursusBundle\Manager\CursusManager;
use Claroline\TagBundle\Manager\TagManager;
use FormaLibre\ReservationBundle\Entity\Resource;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('claroline_cursus_tool')")
 */
class AdminManagementController extends Controller
{
    private $apiManager;
    private $authorization;
    private $configHandler;
    private $cursusManager;
    private $eventDispatcher;
    private $locationManager;
    private $organizationManager;
    private $request;
    private $serializer;
    private $tagManager;
    private $translator;
    private $userManager;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "apiManager"            = @DI\Inject("claroline.manager.api_manager"),
     *     "authorization"         = @DI\Inject("security.authorization_checker"),
     *     "configHandler"         = @DI\Inject("claroline.config.platform_config_handler"),
     *     "cursusManager"         = @DI\Inject("claroline.manager.cursus_manager"),
     *     "eventDispatcher"       = @DI\Inject("event_dispatcher"),
     *     "locationManager"       = @DI\Inject("claroline.manager.organization.location_manager"),
     *     "organizationManager"   = @DI\Inject("claroline.manager.organization.organization_manager"),
     *     "request"               = @DI\Inject("request"),
     *     "serializer"            = @DI\Inject("jms_serializer"),
     *     "tagManager"            = @DI\Inject("claroline.manager.tag_manager"),
     *     "translator"            = @DI\Inject("translator"),
     *     "userManager"           = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager"      = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        ApiManager $apiManager,
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $configHandler,
        CursusManager $cursusManager,
        EventDispatcherInterface $eventDispatcher,
        LocationManager $locationManager,
        OrganizationManager $organizationManager,
        Request $request,
        Serializer $serializer,
        TagManager $tagManager,
        TranslatorInterface $translator,
        UserManager $userManager,
        WorkspaceManager $workspaceManager
    ) {
        $this->apiManager = $apiManager;
        $this->authorization = $authorization;
        $this->configHandler = $configHandler;
        $this->cursusManager = $cursusManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->locationManager = $locationManager;
        $this->organizationManager = $organizationManager;
        $this->request = $request;
        $this->serializer = $serializer;
        $this->tagManager = $tagManager;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @EXT\Route(
     *     "/admin/management/index",
     *     name="claro_cursus_admin_management_index"
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Template()
     *
     * @param User $user
     *
     * @return array
     */
    public function indexAction(User $user)
    {
        $isAdmin = $this->authorization->isGranted('ROLE_ADMIN');
        $organizations = $isAdmin ? [] : $user->getAdministratedOrganizations()->toArray();

        return ['isAuthorized' => $isAdmin || count($organizations) > 0];
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/create",
     *     name="api_post_cursus_creation",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Creates a cursus
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postCursusCreationAction()
    {
        $cursusDatas = $this->request->request->get('cursusDatas', false);
        $worskpace = null;

        if ($cursusDatas['workspace']) {
            $worskpace = $this->workspaceManager->getWorkspaceById($cursusDatas['workspace']);
        }
        $organizations = isset($cursusDatas['organizations']) && count($cursusDatas['organizations']) > 0 ?
            $this->organizationManager->getOrganizationsByIds($cursusDatas['organizations']) :
            [];
        $blocking = is_bool($cursusDatas['blocking']) ? $cursusDatas['blocking'] : $cursusDatas['blocking'] === 'true';
        $createdCursus = $this->cursusManager->createCursus(
            $cursusDatas['title'],
            $cursusDatas['code'],
            null,
            null,
            $cursusDatas['description'],
            $blocking,
            $this->request->files->get('cursusDatas')['icon'],
            $cursusDatas['color'],
            $worskpace,
            $organizations
        );

        $serializedCursus = $this->serializer->serialize(
            $createdCursus,
            'json',
            SerializationContext::create()->setGroups(['api_workspace_min'])
        );

        return new JsonResponse($serializedCursus, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/{parent}/child/create",
     *     name="api_post_cursus_child_creation",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Creates a child cursus
     *
     * @param User   $user
     * @param Cursus $parent
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postCursusChildCreationAction(User $user, Cursus $parent)
    {
        $this->cursusManager->checkCursusAccess($user, $parent);
        $cursusDatas = $this->request->request->get('cursusDatas', false);
        $worskpace = null;

        if ($cursusDatas['workspace']) {
            $worskpace = $this->workspaceManager->getWorkspaceById($cursusDatas['workspace']);
        }

        $blocking = is_bool($cursusDatas['blocking']) ? $cursusDatas['blocking'] : $cursusDatas['blocking'] === 'true';
        $createdCursus = $this->cursusManager->createCursus(
            $cursusDatas['title'],
            $cursusDatas['code'],
            $parent,
            null,
            $cursusDatas['description'],
            $blocking,
            $this->request->files->get('cursusDatas')['icon'],
            $cursusDatas['color'],
            $worskpace
        );
        $serializedCursus = $this->serializer->serialize(
            $createdCursus,
            'json',
            SerializationContext::create()->setGroups(['api_workspace_min'])
        );

        return new JsonResponse($serializedCursus, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/{cursus}/edit",
     *     name="api_put_cursus_edition",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Edits a cursus
     *
     * @param User   $user
     * @param Cursus $cursus
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putCursusEditionAction(User $user, Cursus $cursus)
    {
        $this->cursusManager->checkCursusAccess($user, $cursus);
        $cursusDatas = $this->request->request->get('cursusDatas', false);
        $cursus->setTitle($cursusDatas['title']);
        $cursus->setCode($cursusDatas['code']);
        $cursus->setDescription($cursusDatas['description']);
        $blocking = is_bool($cursusDatas['blocking']) ? $cursusDatas['blocking'] : $cursusDatas['blocking'] === 'true';
        $cursus->setBlocking($blocking);
        $color = $cursusDatas['color'];
        $details = ['color' => $color];
        $cursus->setDetails($details);

        if ($cursusDatas['workspace']) {
            $worskpace = $this->workspaceManager->getWorkspaceById($cursusDatas['workspace']);
            $cursus->setWorkspace($worskpace);
        }
        if ($this->request->files->get('cursusDatas')['icon']) {
            $icon = $this->cursusManager->saveIcon($this->request->files->get('cursusDatas')['icon'], $cursus);
            $cursus->setIcon($icon);
        }
        if (empty($cursus->getParent())) {
            $organizations = isset($cursusDatas['organizations']) && count($cursusDatas['organizations']) > 0 ?
                $this->organizationManager->getOrganizationsByIds($cursusDatas['organizations']) :
                [];
            $this->cursusManager->updateCursusOrganizations($cursus, $organizations);
        }
        $this->cursusManager->persistCursus($cursus);
        $event = new LogCursusEditEvent($cursus);
        $this->eventDispatcher->dispatch('log', $event);
        $serializedCursus = $this->serializer->serialize(
            $cursus,
            'json',
            SerializationContext::create()->setGroups(['api_workspace_min'])
        );

        return new JsonResponse($serializedCursus, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/{cursus}/delete",
     *     name="api_delete_cursus",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Deletes cursus
     *
     * @param User   $user
     * @param Cursus $cursus
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteCursusAction(User $user, Cursus $cursus)
    {
        $this->cursusManager->checkCursusAccess($user, $cursus);
        $serializedCursus = $this->serializer->serialize(
            $cursus,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );
        $this->cursusManager->deleteCursus($cursus);

        return new JsonResponse($serializedCursus, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/import",
     *     name="api_post_cursus_import",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Imports cursus
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postCursusImportAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $file = $this->request->files->get('archive');
        $zip = new \ZipArchive();

        if (empty($file) || !$zip->open($file) || !$zip->getStream('cursus.json') || !$zip->getStream('courses.json')) {
            return new JsonResponse('invalid file', 500);
        }
        $coursesStream = $zip->getStream('courses.json');
        $coursesContents = '';

        while (!feof($coursesStream)) {
            $coursesContents .= fread($coursesStream, 2);
        }
        fclose($coursesStream);
        $courses = json_decode($coursesContents, true);
        $importedCourses = $this->cursusManager->importCourses($courses, $user->getAdministratedOrganizations()->toArray());
        $iconsDir = $this->container->getParameter('claroline.param.thumbnails_directory').'/';

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $name = $zip->getNameIndex($i);

            if (strpos($name, 'icons/') !== 0) {
                continue;
            }
            $iconFileName = $iconsDir.substr($name, 6);
            $stream = $zip->getStream($name);
            $destStream = fopen($iconFileName, 'w');

            while ($data = fread($stream, 1024)) {
                fwrite($destStream, $data);
            }
            fclose($stream);
            fclose($destStream);
        }
        $cursusStream = $zip->getStream('cursus.json');
        $cursuscontents = '';

        while (!feof($cursusStream)) {
            $cursuscontents .= fread($cursusStream, 2);
        }
        fclose($cursusStream);
        $zip->close();
        $cursus = json_decode($cursuscontents, true);
        $rootCursus = $this->cursusManager->importCursus(
            $cursus,
            $user->getAdministratedOrganizations()->toArray(),
            $importedCourses
        );
        $serializedCursus = $this->serializer->serialize(
            $rootCursus,
            'json',
            SerializationContext::create()->setGroups(['api_workspace_min'])
        );

        return new JsonResponse($serializedCursus, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/{cursus}/course/create",
     *     name="api_post_cursus_course_creation",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User   $user
     * @param Cursus $cursus
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postCursusCourseCreateAction(User $user, Cursus $cursus)
    {
        $this->cursusManager->checkCursusAccess($user, $cursus);
        $courseDatas = $this->request->request->get('courseDatas', false);
        $worskpace = null;
        $worskpaceModel = null;
        $publicRegistration = is_bool($courseDatas['publicRegistration']) ?
            $courseDatas['publicRegistration'] :
            $courseDatas['publicRegistration'] === 'true';
        $publicUnregistration = is_bool($courseDatas['publicUnregistration']) ?
            $courseDatas['publicUnregistration'] :
            $courseDatas['publicUnregistration'] === 'true';
        $registrationValidation = is_bool($courseDatas['registrationValidation']) ?
            $courseDatas['registrationValidation'] :
            $courseDatas['registrationValidation'] === 'true';
        $userValidation = is_bool($courseDatas['userValidation']) ?
            $courseDatas['userValidation'] :
            $courseDatas['userValidation'] === 'true';
        $organizationValidation = is_bool($courseDatas['organizationValidation']) ?
            $courseDatas['organizationValidation'] :
            $courseDatas['organizationValidation'] === 'true';
        $withSessionEvent = is_bool($courseDatas['withSessionEvent']) ?
            $courseDatas['withSessionEvent'] :
            $courseDatas['withSessionEvent'] === 'true';
        if ($this->request->files->get('courseDatas')['icon']) {
        }
        $validators = isset($courseDatas['validators']) && count($courseDatas['validators']) > 0 ?
            $this->userManager->getUsersByIds($courseDatas['validators']) :
            [];
        $organizations = isset($courseDatas['organizations']) && count($courseDatas['organizations']) > 0 ?
            $this->organizationManager->getOrganizationsByIds($courseDatas['organizations']) :
            [];
        $createdCourse = $this->cursusManager->createCourse(
            $courseDatas['title'],
            $courseDatas['code'],
            $courseDatas['description'],
            $publicRegistration,
            $publicUnregistration,
            $registrationValidation,
            $courseDatas['tutorRoleName'],
            $courseDatas['learnerRoleName'],
            $worskpaceModel,
            $worskpace,
            $this->request->files->get('courseDatas')['icon'],
            $userValidation,
            $organizationValidation,
            $courseDatas['maxUsers'],
            $courseDatas['defaultSessionDuration'],
            $withSessionEvent,
            $validators,
            $courseDatas['displayOrder'],
            $organizations
        );
        $createdCursus = $this->cursusManager->addCoursesToCursus($cursus, [$createdCourse]);
        $serializedCursus = $this->serializer->serialize(
            $createdCursus,
            'json',
            SerializationContext::create()->setGroups(['api_workspace_min'])
        );

        return new JsonResponse($serializedCursus, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/course/create",
     *     name="api_post_course_creation",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postCourseCreateAction()
    {
        $courseDatas = $this->request->request->get('courseDatas', false);
        $worskpace = null;
        $worskpaceModel = null;
        $publicRegistration = is_bool($courseDatas['publicRegistration']) ?
            $courseDatas['publicRegistration'] :
            $courseDatas['publicRegistration'] === 'true';
        $publicUnregistration = is_bool($courseDatas['publicUnregistration']) ?
            $courseDatas['publicUnregistration'] :
            $courseDatas['publicUnregistration'] === 'true';
        $registrationValidation = is_bool($courseDatas['registrationValidation']) ?
            $courseDatas['registrationValidation'] :
            $courseDatas['registrationValidation'] === 'true';
        $userValidation = is_bool($courseDatas['userValidation']) ?
            $courseDatas['userValidation'] :
            $courseDatas['userValidation'] === 'true';
        $organizationValidation = is_bool($courseDatas['organizationValidation']) ?
            $courseDatas['organizationValidation'] :
            $courseDatas['organizationValidation'] === 'true';
        $withSessionEvent = is_bool($courseDatas['withSessionEvent']) ?
            $courseDatas['withSessionEvent'] :
            $courseDatas['withSessionEvent'] === 'true';

        if ($courseDatas['workspace']) {
            $worskpace = $this->workspaceManager->getWorkspaceById($courseDatas['workspace']);
        }
        if ($courseDatas['workspaceModel']) {
            $worskpaceModel = $this->workspaceManager->getOneByCode($courseDatas['workspaceModel']);
        }

        $validators = isset($courseDatas['validators']) && count($courseDatas['validators']) > 0 ?
            $this->userManager->getUsersByIds($courseDatas['validators']) :
            [];
        $organizations = isset($courseDatas['organizations']) && count($courseDatas['organizations']) > 0 ?
            $this->organizationManager->getOrganizationsByIds($courseDatas['organizations']) :
            [];
        $createdCourse = $this->cursusManager->createCourse(
            $courseDatas['title'],
            $courseDatas['code'],
            $courseDatas['description'],
            $publicRegistration,
            $publicUnregistration,
            $registrationValidation,
            $courseDatas['tutorRoleName'],
            $courseDatas['learnerRoleName'],
            $worskpaceModel,
            $worskpace,
            $this->request->files->get('courseDatas')['icon'],
            $userValidation,
            $organizationValidation,
            $courseDatas['maxUsers'],
            $courseDatas['defaultSessionDuration'],
            $withSessionEvent,
            $validators,
            $courseDatas['displayOrder'],
            $organizations
        );
        $serializedCourse = $this->serializer->serialize(
            $createdCourse,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedCourse, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/{cursus}/course/{course}/add",
     *     name="api_post_cursus_course_add",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User   $user
     * @param Cursus $cursus
     * @param Course $course
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postCursusCourseAddAction(User $user, Cursus $cursus, Course $course)
    {
        $this->cursusManager->checkCursusAccess($user, $cursus);
        $this->cursusManager->checkCourseAccess($user, $course);
        $createdCursus = $this->cursusManager->addCoursesToCursus($cursus, [$course]);
        $serializedCursus = $this->serializer->serialize(
            $createdCursus,
            'json',
            SerializationContext::create()->setGroups(['api_workspace_min'])
        );

        return new JsonResponse($serializedCursus, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/course/{course}/edit",
     *     name="api_put_course_edition",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Edits a course
     *
     * @param User   $user
     * @param Course $course
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putCourseEditionAction(User $user, Course $course)
    {
        $this->cursusManager->checkCourseAccess($user, $course);
        $courseDatas = $this->request->request->get('courseDatas', false);
        $course->setTitle($courseDatas['title']);
        $course->setCode($courseDatas['code']);
        $description = $courseDatas['description'] ? $courseDatas['description'] : null;
        $course->setDescription($description);
        $publicRegistration = is_bool($courseDatas['publicRegistration']) ?
            $courseDatas['publicRegistration'] :
            $courseDatas['publicRegistration'] === 'true';
        $publicUnregistration = is_bool($courseDatas['publicUnregistration']) ?
            $courseDatas['publicUnregistration'] :
            $courseDatas['publicUnregistration'] === 'true';
        $registrationValidation = is_bool($courseDatas['registrationValidation']) ?
            $courseDatas['registrationValidation'] :
            $courseDatas['registrationValidation'] === 'true';
        $userValidation = is_bool($courseDatas['userValidation']) ?
            $courseDatas['userValidation'] :
            $courseDatas['userValidation'] === 'true';
        $organizationValidation = is_bool($courseDatas['organizationValidation']) ?
            $courseDatas['organizationValidation'] :
            $courseDatas['organizationValidation'] === 'true';
        $withSessionEvent = is_bool($courseDatas['withSessionEvent']) ?
            $courseDatas['withSessionEvent'] :
            $courseDatas['withSessionEvent'] === 'true';
        $course->setPublicRegistration($publicRegistration);
        $course->setPublicUnregistration($publicUnregistration);
        $course->setRegistrationValidation($registrationValidation);
        $tutorRoleName = $courseDatas['tutorRoleName'] ? $courseDatas['tutorRoleName'] : null;
        $course->setTutorRoleName($tutorRoleName);
        $learnerRoleName = $courseDatas['learnerRoleName'] ? $courseDatas['learnerRoleName'] : null;
        $course->setLearnerRoleName($learnerRoleName);

        if ($courseDatas['workspace']) {
            $worskpace = $this->workspaceManager->getWorkspaceById($courseDatas['workspace']);
            $course->setWorkspace($worskpace);
        } else {
            $course->setWorkspace(null);
        }
        if ($courseDatas['workspaceModel']) {
            $worskpaceModel = $this->workspaceManager->getWorkspaceById($courseDatas['workspaceModel']);
            $course->setWorkspaceModel($worskpaceModel);
        } else {
            $course->setWorkspaceModel(null);
        }
        if ($this->request->files->get('courseDatas')['icon']) {
            $icon = $this->cursusManager->saveIcon($this->request->files->get('courseDatas')['icon'], $course);
            $course->setIcon($icon);
        }
        $course->setUserValidation($userValidation);
        $course->setOrganizationValidation($organizationValidation);
        $maxUsers = $courseDatas['maxUsers'] ? $courseDatas['maxUsers'] : null;
        $course->setMaxUsers($maxUsers);
        $course->setDefaultSessionDuration($courseDatas['defaultSessionDuration']);
        $course->setWithSessionEvent($withSessionEvent);
        $course->setDisplayOrder($courseDatas['displayOrder']);
        $course->emptyValidators();
        $validators = isset($courseDatas['validators']) && count($courseDatas['validators']) > 0 ?
            $this->userManager->getUsersByIds($courseDatas['validators']) :
            [];

        foreach ($validators as $validator) {
            $course->addValidator($validator);
        }
        $course->emptyOrganizations();
        $organizations = isset($courseDatas['organizations']) && count($courseDatas['organizations']) > 0 ?
            $this->organizationManager->getOrganizationsByIds($courseDatas['organizations']) :
            [];

        foreach ($organizations as $organization) {
            $course->addOrganization($organization);
        }
        $this->cursusManager->persistCourse($course);
        $event = new LogCourseEditEvent($course);
        $this->eventDispatcher->dispatch('log', $event);
        $serializedCourse = $this->serializer->serialize(
            $course,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedCourse, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/course/{course}/delete",
     *     name="api_delete_course",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Deletes course
     *
     * @param User   $user
     * @param Course $course
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteCourseAction(User $user, Course $course)
    {
        $this->cursusManager->checkCourseAccess($user, $course);
        $serializedCourse = $this->serializer->serialize(
            $course,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );
        $this->cursusManager->deleteCourse($course);

        return new JsonResponse($serializedCourse, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/courses/import",
     *     name="api_post_courses_import",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Imports courses
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postCoursesImportAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $file = $this->request->files->get('archive');
        $zip = new \ZipArchive();

        if (empty($file) || !$zip->open($file) || !$zip->getStream('courses.json')) {
            return new JsonResponse('invalid file', 500);
        }
        $coursesStream = $zip->getStream('courses.json');
        $coursesContents = '';

        while (!feof($coursesStream)) {
            $coursesContents .= fread($coursesStream, 2);
        }
        fclose($coursesStream);
        $courses = json_decode($coursesContents, true);
        $importedCourses = $this->cursusManager->importCourses(
            $courses,
            $user->getAdministratedOrganizations()->toArray(),
            false
        );
        $iconsDir = $this->container->getParameter('claroline.param.thumbnails_directory').'/';

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $name = $zip->getNameIndex($i);

            if (strpos($name, 'icons/') !== 0) {
                continue;
            }
            $iconFileName = $iconsDir.substr($name, 6);
            $stream = $zip->getStream($name);
            $destStream = fopen($iconFileName, 'w');

            while ($data = fread($stream, 1024)) {
                fwrite($destStream, $data);
            }
            fclose($stream);
            fclose($destStream);
        }
        $zip->close();
        $serializedCourses = $this->serializer->serialize(
            $importedCourses,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );

        return new JsonResponse($serializedCourses, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/course/{course}/get/by/id",
     *     name="api_get_course_by_id",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns the course
     *
     * @param User   $user
     * @param Course $course
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCourseByIdAction(User $user, Course $course)
    {
        $this->cursusManager->checkCourseAccess($user, $course);
        $serializedCourse = $this->serializer->serialize(
            $course,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedCourse, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/get/by/code/{code}/without/id/{id}",
     *     name="api_get_cursus_by_code_without_id",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns the cursus
     *
     * @param User   $user
     * @param string $code
     * @param int    $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCursusByCodeWithoutIdAction(User $user, $code, $id = 0)
    {
        $cursus = $this->cursusManager->getCursusByCodeWithoutId($code, $id);
        $serializedCursus = $this->serializer->serialize(
            $cursus,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );

        return new JsonResponse($serializedCursus, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/course/get/by/code/{code}/without/id/{id}",
     *     name="api_get_course_by_code_without_id",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns the course
     *
     * @param User   $user
     * @param string $code
     * @param int    $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCourseByCodeWithoutIdAction(User $user, $code, $id = 0)
    {
        $course = $this->cursusManager->getCourseByCodeWithoutId($code, $id);
        $serializedCourse = $this->serializer->serialize(
            $course,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );

        return new JsonResponse($serializedCourse, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/get/by/id",
     *     name="api_get_session_by_id",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns the session
     *
     * @param User          $user
     * @param CourseSession $session
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSessionByIdAction(User $user, CourseSession $session)
    {
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $serializedSession = $this->serializer->serialize(
            $session,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedSession, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/course/{course}/session/create",
     *     name="api_post_session_creation",
     *     options = {"expose"=true}
     * )
     *
     * @param User   $user
     * @param Course $course
     *
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function postSessionCreateAction(User $user, Course $course)
    {
        $this->cursusManager->checkCourseAccess($user, $course);
        $sessionDatas = $this->request->request->get('sessionDatas', false);
        $defaultSession = is_bool($sessionDatas['defaultSession']) ?
            $sessionDatas['defaultSession'] :
            $sessionDatas['defaultSession'] === 'true';
        $publicRegistration = is_bool($sessionDatas['publicRegistration']) ?
            $sessionDatas['publicRegistration'] :
            $sessionDatas['publicRegistration'] === 'true';
        $publicUnregistration = is_bool($sessionDatas['publicUnregistration']) ?
            $sessionDatas['publicUnregistration'] :
            $sessionDatas['publicUnregistration'] === 'true';
        $registrationValidation = is_bool($sessionDatas['registrationValidation']) ?
            $sessionDatas['registrationValidation'] :
            $sessionDatas['registrationValidation'] === 'true';
        $userValidation = is_bool($sessionDatas['userValidation']) ?
            $sessionDatas['userValidation'] :
            $sessionDatas['userValidation'] === 'true';
        $organizationValidation = is_bool($sessionDatas['organizationValidation']) ?
            $sessionDatas['organizationValidation'] :
            $sessionDatas['organizationValidation'] === 'true';
        $trimmedStartDate = trim($sessionDatas['startDate'], 'Zz');
        $trimmedEndDate = trim($sessionDatas['endDate'], 'Zz');
        $startDate = new \DateTime($trimmedStartDate);
        $endDate = new \DateTime($trimmedEndDate);
        $cursusIds = $sessionDatas['cursus'] ? $sessionDatas['cursus'] : [];
        $validatorsIds = $sessionDatas['validators'] ? $sessionDatas['validators'] : [];
        $cursus = $this->cursusManager->getCursusByIds($cursusIds);
        $validators = $this->userManager->getUsersByIds($validatorsIds);
        $createdSession = $this->cursusManager->createCourseSession(
            $course,
            $sessionDatas['name'],
            $sessionDatas['description'],
            $cursus,
            null,
            $startDate,
            $endDate,
            $defaultSession,
            $publicRegistration,
            $publicUnregistration,
            $registrationValidation,
            $userValidation,
            $organizationValidation,
            $sessionDatas['maxUsers'],
            0,
            $validators,
            $sessionDatas['eventRegistrationType'],
            $sessionDatas['displayOrder'],
            $sessionDatas['color']
        );
        $serializedSession = $this->serializer->serialize(
            $createdSession,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedSession, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/edit",
     *     name="api_put_session_edition",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Edits a session
     *
     * @param User          $user
     * @param CourseSession $session
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putSessionEditionAction(User $user, CourseSession $session)
    {
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $sessionDatas = $this->request->request->get('sessionDatas', false);
        $defaultSession = is_bool($sessionDatas['defaultSession']) ?
            $sessionDatas['defaultSession'] :
            $sessionDatas['defaultSession'] === 'true';
        $publicRegistration = is_bool($sessionDatas['publicRegistration']) ?
            $sessionDatas['publicRegistration'] :
            $sessionDatas['publicRegistration'] === 'true';
        $publicUnregistration = is_bool($sessionDatas['publicUnregistration']) ?
            $sessionDatas['publicUnregistration'] :
            $sessionDatas['publicUnregistration'] === 'true';
        $registrationValidation = is_bool($sessionDatas['registrationValidation']) ?
            $sessionDatas['registrationValidation'] :
            $sessionDatas['registrationValidation'] === 'true';
        $userValidation = is_bool($sessionDatas['userValidation']) ?
            $sessionDatas['userValidation'] :
            $sessionDatas['userValidation'] === 'true';
        $organizationValidation = is_bool($sessionDatas['organizationValidation']) ?
            $sessionDatas['organizationValidation'] :
            $sessionDatas['organizationValidation'] === 'true';
        $trimmedStartDate = trim($sessionDatas['startDate'], 'Zz');
        $trimmedEndDate = trim($sessionDatas['endDate'], 'Zz');
        $startDate = new \DateTime($trimmedStartDate);
        $endDate = new \DateTime($trimmedEndDate);
        $session->setName($sessionDatas['name']);
        $session->setStartDate($startDate);
        $session->setEndDate($endDate);
        $session->setDescription($sessionDatas['description']);
        $session->setDefaultSession($defaultSession);
        $session->setPublicRegistration($publicRegistration);
        $session->setPublicUnregistration($publicUnregistration);
        $session->setUserValidation($userValidation);
        $session->setMaxUsers($sessionDatas['maxUsers']);
        $session->setOrganizationValidation($organizationValidation);
        $session->setRegistrationValidation($registrationValidation);
        $session->setEventRegistrationType($sessionDatas['eventRegistrationType']);
        $session->setDisplayOrder($sessionDatas['displayOrder']);
        $details = $session->getDetails();

        if (is_null($details)) {
            $details = [];
        }
        $details['color'] = $sessionDatas['color'];
        $session->setDetails($details);
        $cursusIds = $sessionDatas['cursus'] ? $sessionDatas['cursus'] : [];
        $validatorsIds = $sessionDatas['validators'] ? $sessionDatas['validators'] : [];
        $cursus = $this->cursusManager->getCursusByIds($cursusIds);
        $validators = $this->userManager->getUsersByIds($validatorsIds);
        $session->emptyCursus();
        $session->emptyValidators();

        foreach ($cursus as $c) {
            $session->addCursus($c);
        }
        foreach ($validators as $validator) {
            $session->addValidator($validator);
        }
        $this->cursusManager->persistCourseSession($session);
        $event = new LogCourseSessionEditEvent($session);
        $this->eventDispatcher->dispatch('log', $event);
        $serializedSession = $this->serializer->serialize(
            $session,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedSession, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/mode/{mode}/delete",
     *     name="api_delete_session",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Deletes session
     *
     * @param User          $user
     * @param CourseSession $session
     * @param int           $mode
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteSessionAction(User $user, CourseSession $session, $mode = 0)
    {
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $serializedSession = $this->serializer->serialize(
            $session,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );
        $withWorkspace = (intval($mode) === 1);
        $this->cursusManager->deleteCourseSession($session, $withWorkspace);

        return new JsonResponse($serializedSession, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/course/{course}/session/{session}/default/reset",
     *     name="api_put_session_default_reset",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Deletes session
     *
     * @param User          $user
     * @param Course        $course
     * @param CourseSession $session
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function resetSessionsDefaultAction(User $user, Course $course, CourseSession $session)
    {
        $this->cursusManager->checkCourseAccess($user, $course);
        $this->cursusManager->resetDefaultSessionByCourse($course, $session);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/event/{sessionEvent}/get/by/id",
     *     name="api_get_session_event_by_id",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns the session event
     *
     * @param User         $user
     * @param SessionEvent $sessionEvent
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSessionEventByIdAction(User $user, SessionEvent $sessionEvent)
    {
        $this->cursusManager->checkCourseAccess($user, $sessionEvent->getSession()->getCourse());
        $serializedSessionEvent = $this->serializer->serialize(
            $sessionEvent,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedSessionEvent, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/event/create",
     *     name="api_post_session_event_creation",
     *     options = {"expose"=true}
     * )
     *
     * @param User          $user
     * @param CourseSession $session
     *
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function postSessionEventCreateAction(User $user, CourseSession $session)
    {
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $sessionEventData = $this->request->request->get('sessionEventData', false);
        $trimmedStartDate = trim($sessionEventData['startDate'], 'Zz');
        $trimmedEndDate = trim($sessionEventData['endDate'], 'Zz');
        $startDate = new \DateTime($trimmedStartDate);
        $endDate = new \DateTime($trimmedEndDate);
        $location = null;
        $locationResource = null;
        $tutorsIds = $sessionEventData['tutors'] ? $sessionEventData['tutors'] : [];
        $tutors = $this->userManager->getUsersByIds($tutorsIds);

        if ($sessionEventData['location']) {
            $location = $this->locationManager->getLocationById($sessionEventData['location']);
        }
        if ($sessionEventData['locationResource']) {
            $locationResource = $this->cursusManager->getReservationResourceById($sessionEventData['locationResource']);
        }
        $type = $sessionEventData['type'] ? SessionEvent::TYPE_EVENT : SessionEvent::TYPE_NONE;
        $eventSet = $sessionEventData['eventSet'] && $sessionEventData['registrationType'] === CourseSession::REGISTRATION_PUBLIC ?
            $this->cursusManager->getSessionEventSet($session, $sessionEventData['eventSet']) :
             null;
        $createdSessionEvent = $this->cursusManager->createSessionEvent(
            $session,
            $sessionEventData['name'],
            $sessionEventData['description'],
            $startDate,
            $endDate,
            $location,
            $sessionEventData['locationExtra'],
            $locationResource,
            $tutors,
            $sessionEventData['registrationType'],
            $sessionEventData['maxUsers'],
            $type,
            $eventSet
        );
        $serializedSessionEvent = $this->serializer->serialize(
            $createdSessionEvent,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedSessionEvent, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/event/{sessionEvent}/edit",
     *     name="api_put_session_event_edition",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Edits a session event
     *
     * @param User         $user
     * @param SessionEvent $sessionEvent
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putSessionEventEditionAction(User $user, SessionEvent $sessionEvent)
    {
        $this->cursusManager->checkCourseAccess($user, $sessionEvent->getSession()->getCourse());
        $sessionEventData = $this->request->request->get('sessionEventData', false);
        $trimmedStartDate = trim($sessionEventData['startDate'], 'Zz');
        $trimmedEndDate = trim($sessionEventData['endDate'], 'Zz');
        $startDate = new \DateTime($trimmedStartDate);
        $endDate = new \DateTime($trimmedEndDate);
        $sessionEvent->setName($sessionEventData['name']);
        $sessionEvent->setStartDate($startDate);
        $sessionEvent->setEndDate($endDate);
        $sessionEvent->setDescription($sessionEventData['description']);
        $sessionEvent->setLocationExtra($sessionEventData['locationExtra']);
        $sessionEvent->setRegistrationType($sessionEventData['registrationType']);
        $sessionEvent->setMaxUsers($sessionEventData['maxUsers']);
        $sessionEvent->setLocationResource(null);
        $sessionEvent->setLocation(null);

        if ($sessionEventData['location']) {
            $location = $this->locationManager->getLocationById($sessionEventData['location']);

            if (!is_null($location)) {
                $sessionEvent->setLocation($location);
            }
        }
        if ($sessionEventData['locationResource']) {
            $locationResource = $this->cursusManager->getReservationResourceById($sessionEventData['locationResource']);

            if (!is_null($locationResource)) {
                $sessionEvent->setLocationResource($locationResource);
            }
        }
        $sessionEvent->emptyTutors();
        $tutorsIds = $sessionEventData['tutors'] ? $sessionEventData['tutors'] : [];
        $tutors = $this->userManager->getUsersByIds($tutorsIds);

        foreach ($tutors as $tutor) {
            $sessionEvent->addTutor($tutor);
        }
        $type = $sessionEventData['type'] ? SessionEvent::TYPE_EVENT : SessionEvent::TYPE_NONE;
        $sessionEvent->setType($type);
        $eventSet = $sessionEventData['eventSet'] && $sessionEventData['registrationType'] === CourseSession::REGISTRATION_PUBLIC ?
            $this->cursusManager->getSessionEventSet($sessionEvent->getSession(), $sessionEventData['eventSet']) :
             null;
        $sessionEvent->setEventSet($eventSet);
        $this->cursusManager->persistSessionEvent($sessionEvent);
        $event = new LogSessionEventEditEvent($sessionEvent);
        $this->eventDispatcher->dispatch('log', $event);
        $this->cursusManager->checkPendingSessionEventUsers($sessionEvent);

        if ($sessionEvent->getRegistrationType() === CourseSession::REGISTRATION_AUTO) {
            $this->cursusManager->registerSessionUsersToSessionEvent($sessionEvent);
        }
        $serializedSessionEvent = $this->serializer->serialize(
            $sessionEvent,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedSessionEvent, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/event/{sessionEvent}/delete",
     *     name="api_delete_session_event",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Deletes session event
     *
     * @param User         $user
     * @param SessionEvent $sessionEvent
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteSessionEventAction(User $user, SessionEvent $sessionEvent)
    {
        $this->cursusManager->checkCourseAccess($user, $sessionEvent->getSession()->getCourse());
        $serializedSessionEvent = $this->serializer->serialize(
            $sessionEvent,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );
        $this->cursusManager->deleteSessionEvent($sessionEvent);

        return new JsonResponse($serializedSessionEvent, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/user/type/{type}",
     *     name="api_get_session_users_by_session_and_type",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Get the session learners list
     *
     * @param User          $user
     * @param CourseSession $session
     * @param int           $type
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSessionUsersBySessionAndTypeAction(User $user, CourseSession $session, $type)
    {
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $learners = $this->cursusManager->getSessionUsersBySessionAndType($session, $type);
        $serializedLearners = $this->serializer->serialize(
            $learners,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedLearners, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/users",
     *     name="api_get_session_users_by_session",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Get the session users list
     *
     * @param User          $user
     * @param CourseSession $session
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSessionUsersBySessionAction(User $user, CourseSession $session)
    {
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $sessionUsers = $this->cursusManager->getSessionUsersBySession($session);
        $serializedSessionUsers = $this->serializer->serialize(
            $sessionUsers,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedSessionUsers, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/groups",
     *     name="api_get_session_groups_by_session",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Get the session groups list
     *
     * @param User          $user
     * @param CourseSession $session
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSessionGroupsBySessionAction(User $user, CourseSession $session)
    {
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $sessionGroups = $this->cursusManager->getSessionGroupsBySession($session);
        $serializedSessionGroups = $this->serializer->serialize(
            $sessionGroups,
            'json',
            SerializationContext::create()->setGroups(['api_group_min'])
        );

        return new JsonResponse($serializedSessionGroups, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/pending/users",
     *     name="api_get_session_pending_users_by_session",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Get the session pending users list
     *
     * @param User          $user
     * @param CourseSession $session
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSessionPendingUsersBySessionAction(User $user, CourseSession $session)
    {
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $pendingUsers = $this->cursusManager->getSessionQueuesBySession($session);
        $serializedPendingUsers = $this->serializer->serialize(
            $pendingUsers,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedPendingUsers, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/user/{sessionUser}/delete",
     *     name="api_delete_session_user",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Deletes a session user
     *
     * @param User              $user
     * @param CourseSessionUser $sessionUser
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteSessionUserAction(User $user, CourseSessionUser $sessionUser)
    {
        $this->cursusManager->checkCourseAccess($user, $sessionUser->getSession()->getCourse());
        $serializedSessionUser = $this->serializer->serialize(
            $sessionUser,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );
        $this->cursusManager->unregisterUsersFromSession([$sessionUser]);

        return new JsonResponse($serializedSessionUser, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/group/{sessionGroup}/delete",
     *     name="api_delete_session_group",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Deletes a session group
     *
     * @param User               $user
     * @param CourseSessionGroup $sessionGroup
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteSessionGroupAction(User $user, CourseSessionGroup $sessionGroup)
    {
        $this->cursusManager->checkCourseAccess($user, $sessionGroup->getSession()->getCourse());
        $serializedSessionGroup = $this->serializer->serialize(
            $sessionGroup,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );
        $serializedSessionUsers = $this->cursusManager->unregisterGroupFromSession($sessionGroup);

        return new JsonResponse(['group' => $serializedSessionGroup, 'users' => $serializedSessionUsers], 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/registration/queue/{queue}/accept",
     *     name="api_accept_session_registration_queue",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Accepts session registration queue
     *
     * @param User                           $user
     * @param CourseSessionRegistrationQueue $queue
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function acceptSessionRegistrationQueueAction(User $user, CourseSessionRegistrationQueue $queue)
    {
        $session = $queue->getSession();
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $queueUser = $queue->getUser();
        $results = $this->cursusManager->registerUsersToSession($session, [$queueUser], CourseSessionUser::LEARNER, true);

        if ($results['status'] === 'success') {
            $serializedQueue = $this->serializer->serialize(
                $queue,
                'json',
                SerializationContext::create()->setGroups(['api_cursus'])
            );
            $results['queue'] = $serializedQueue;
            $this->cursusManager->deleteSessionQueue($queue);
            $this->cursusManager->sendSessionRegistrationConfirmationMessage($queueUser, $session, 'validated');
        }

        return new JsonResponse($results, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/registration/queue/{queue}/delete",
     *     name="api_delete_session_registration_queue",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Deletes session registration queue
     *
     * @param User                           $user
     * @param CourseSessionRegistrationQueue $queue
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteSessionRegistrationQueueAction(User $user, CourseSessionRegistrationQueue $queue)
    {
        $this->cursusManager->checkCourseAccess($user, $queue->getSession()->getCourse());
        $serializedQueue = $this->serializer->serialize(
            $queue,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );
        $this->cursusManager->declineSessionQueue($queue);

        return new JsonResponse($serializedQueue, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/unregistered/users/type/{userType}",
     *     name="api_get_session_unregistered_users",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Displays the list of users who are not registered to the session
     *
     * @param User          $user
     * @param CourseSession $session
     * @param int           $userType
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSessionUnregisteredUsersAction(User $user, CourseSession $session, $userType = 0)
    {
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $users = $this->cursusManager->getUnregisteredUsersBySession($user, $session, $userType, 'lastName');
        $serializedUsers = $this->serializer->serialize(
            $users,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedUsers, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/unregistered/groups/type/{groupType}",
     *     name="api_get_session_unregistered_groups",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Displays the list of groups that are not registered to the session
     *
     * @param User          $user
     * @param CourseSession $session
     * @param int           $groupType
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSessionUnregisteredGroupsAction(User $user, CourseSession $session, $groupType = 0)
    {
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $groups = $this->cursusManager->getUnregisteredGroupsBySession($user, $session, $groupType);
        $serializedGroups = $this->serializer->serialize(
            $groups,
            'json',
            SerializationContext::create()->setGroups(['api_group_min'])
        );

        return new JsonResponse($serializedGroups, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/user/{user}/type/{userType}/register",
     *     name="api_post_session_user_registration",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", converter="current_user")
     *
     * Registers an user to a session
     *
     * @param User          $authenticatedUser
     * @param CourseSession $session
     * @param User          $user
     * @param int           $userType
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postSessionUserRegisterAction(User $authenticatedUser, CourseSession $session, User $user, $userType = 0)
    {
        $this->cursusManager->checkCourseAccess($authenticatedUser, $session->getCourse());
        $results = $this->cursusManager->registerUsersToSession($session, [$user], $userType);

        return new JsonResponse($results, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/group/{group}/type/{groupType}/register",
     *     name="api_post_session_group_registration",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Registers a group to a session
     *
     * @param User          $user
     * @param CourseSession $session
     * @param Group         $group
     * @param int           $groupType
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postSessionGroupRegisterAction(User $user, CourseSession $session, Group $group, $groupType = 0)
    {
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $results = $this->cursusManager->registerGroupToSession($session, $group, $groupType);

        return new JsonResponse($results, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/event/{sessionEvent}/unregistered/users",
     *     name="api_get_session_event_unregistered_users",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Displays the list of users who are not registered to the session event
     *
     * @param User         $user
     * @param SessionEvent $sessionEvent
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSessionEventUnregisteredUsersAction(User $user, SessionEvent $sessionEvent)
    {
        $this->cursusManager->checkCourseAccess($user, $sessionEvent->getSession()->getCourse());
        $users = $this->cursusManager->getUnregisteredUsersBySessionEvent($sessionEvent);
        $serializedUsers = $this->serializer->serialize(
            $users,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedUsers, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/event/{sessionEvent}/user/{user}/register",
     *     name="api_post_session_event_user_registration",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", converter="current_user")
     *
     * Registers an user to a session event
     *
     * @param User         $authenticatedUser
     * @param SessionEvent $sessionEvent
     * @param User         $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postSessionEventUserRegisterAction(User $authenticatedUser, SessionEvent $sessionEvent, User $user)
    {
        $this->cursusManager->checkCourseAccess($authenticatedUser, $sessionEvent->getSession()->getCourse());
        $results = $this->cursusManager->registerUsersToSessionEvent($sessionEvent, [$user]);

        return new JsonResponse($results, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/event/{sessionEvent}/users",
     *     name="api_get_session_event_users_by_session_event",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Get the users list of the session event
     *
     * @param User         $user
     * @param SessionEvent $sessionEvent
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSessionEventUsersBySessionEventAction(User $user, SessionEvent $sessionEvent)
    {
        $this->cursusManager->checkCourseAccess($user, $sessionEvent->getSession()->getCourse());
        $sessionEventUsers = $this->cursusManager->getSessionEventUsersBySessionEvent($sessionEvent);
        $serializedSessionEventUsers = $this->serializer->serialize(
            $sessionEventUsers,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedSessionEventUsers, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/event/user/{sessionEventUser}/delete",
     *     name="api_delete_session_event_user",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Deletes an user from session event
     *
     * @param User             $user
     * @param SessionEventUser $sessionEventUser
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteSessionEventUserAction(User $user, SessionEventUser $sessionEventUser)
    {
        $this->cursusManager->checkCourseAccess($user, $sessionEventUser->getSessionEvent()->getSession()->getCourse());
        $serializedSessionEventUser = $this->serializer->serialize(
            $sessionEventUser,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );
        $this->cursusManager->unregisterUsersFromSessionEvent([$sessionEventUser]);

        return new JsonResponse($serializedSessionEventUser, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/validators/roles",
     *     name="api_get_validators_roles",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Retrieves required roles to be validator
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getValidatorsRolesAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $roles = $this->cursusManager->getValidatorsRoles();
        $serializedRoles = $this->serializer->serialize(
            $roles,
            'json',
            SerializationContext::create()->setGroups(['api_role'])
        );

        return new JsonResponse($serializedRoles, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/worskpaces",
     *     name="api_get_workspaces",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Retrieves workspaces list for an user
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getWorkspacesAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $workspaces = $this->cursusManager->getWorkspacesListForCurrentUser();
        $serializedWorkspaces = $this->serializer->serialize(
            $workspaces,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedWorkspaces, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/worskpace/models",
     *     name="api_get_workspace_models",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Retrieves workspace models list for an user
     *
     * @param User $user
     */
    public function getWorkspaceModelsAction(User $user)
    {
        $data = $this->get('claroline.API.finder')->search(
            'Claroline\CoreBundle\Entity\Workspace\Workspace', [
                'limit' => 100,
                'filters' => ['model' => true],
            ]
        );

        return new JsonResponse($data['results'], 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/locations",
     *     name="api_get_cursus_locations",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Retrieves locations list
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getLocationsAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $locations = $this->locationManager->getByTypes([Location::TYPE_DEPARTMENT, Location::TYPE_TRAINING]);
        $serializedLocations = $this->serializer->serialize(
            $locations,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedLocations, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/reservation/resources",
     *     name="api_get_reservation_resources",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Retrieves reservation resources list
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getReservationResourcesAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $reservationResources = $this->cursusManager->getAllReservationResources();
        $serializedResources = $this->serializer->serialize(
            $reservationResources,
            'json',
            SerializationContext::create()->setGroups(['api_reservation'])
        );

        return new JsonResponse($serializedResources, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/reservation/resources",
     *     name="api_get_cursus_reservation_resources",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Retrieves cursus reservation resources list
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCursusReservationResourcesAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $options = [
            'tag' => 'cursus_location',
            'strict' => true,
            'class' => 'FormaLibre\ReservationBundle\Entity\Resource',
            'object_response' => true,
            'ordered_by' => 'name',
            'order' => 'ASC',
        ];
        $event = $this->eventDispatcher->dispatch('claroline_retrieve_tagged_objects', new GenericDataEvent($options));
        $resources = $event->getResponse();
        $serializedResources = $this->serializer->serialize(
            $resources,
            'json',
            SerializationContext::create()->setGroups(['api_reservation'])
        );

        return new JsonResponse($serializedResources, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/reservation/resource/{resource}/tag/create",
     *     name="api_post_cursus_reservation_resources_tag",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Tags reservation resource
     *
     * @param User     $user
     * @param resource $resource
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postReservationResourceTagAction(User $user, Resource $resource)
    {
        $this->cursusManager->checkAccess($user);
        $options = ['tag' => ['cursus_location'], 'object' => $resource];
        $this->eventDispatcher->dispatch('claroline_tag_object', new GenericDataEvent($options));

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/reservation/resource/{resource}/tag/delete",
     *     name="api_delete_cursus_reservation_resources_tag",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Removes tag from reservation resource
     *
     * @param User     $user
     * @param resource $resource
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteReservationResourceTagAction(User $user, Resource $resource)
    {
        $this->cursusManager->checkAccess($user);
        $this->tagManager->removeTaggedObjectByTagNameAndObjectIdAndClass(
            'cursus_location',
            $resource->getId(),
            'FormaLibre\ReservationBundle\Entity\Resource'
        );

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/general/parameters/retrieve",
     *     name="api_get_cursus_general_parameters",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns the general parameters
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getGeneralParametersAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $datas = [];
        $datas['disableInvitations'] = $this->configHandler->hasParameter('cursus_disable_invitations') ?
            $this->configHandler->getParameter('cursus_disable_invitations') :
            false;
        $datas['disableCertificates'] = $this->configHandler->hasParameter('cursus_disable_certificates') ?
            $this->configHandler->getParameter('cursus_disable_certificates') :
            false;
        $datas['disableSessionEventRegistration'] = $this->configHandler->hasParameter('cursus_disable_session_event_registration') ?
            $this->configHandler->getParameter('cursus_disable_session_event_registration') :
            true;
        $datas['enableCoursesProfileTab'] = $this->configHandler->hasParameter('cursus_enable_courses_profile_tab') ?
            $this->configHandler->getParameter('cursus_enable_courses_profile_tab') :
            false;
        $datas['enableWsInCoursesProfileTab'] = $this->configHandler->hasParameter('cursus_enable_ws_in_courses_profile_tab') ?
            $this->configHandler->getParameter('cursus_enable_ws_in_courses_profile_tab') :
            false;
        $datas['sessionDefaultTotal'] = $this->configHandler->hasParameter('cursus_session_default_total') ?
            $this->configHandler->getParameter('cursus_session_default_total') :
            false;
        $datas['sessionDefaultDuration'] = $this->configHandler->hasParameter('cursus_session_default_duration') ?
            $this->configHandler->getParameter('cursus_session_default_duration') :
            1;
        $datas['displayUserEventsInDesktopAgenda'] = $this->configHandler->hasParameter('cursus_display_user_events_in_desktop_agenda') ?
            $this->configHandler->getParameter('cursus_display_user_events_in_desktop_agenda') :
            false;

        return new JsonResponse($datas, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/general/parameters/register",
     *     name="api_post_cursus_general_parameters",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Sets the general parameters
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postGeneralParametersAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $parameters = $this->request->request->get('parameters', false);
        $this->configHandler->setParameter('cursus_disable_invitations', $parameters['disableInvitations']);
        $this->configHandler->setParameter('cursus_disable_certificates', $parameters['disableCertificates']);
        $this->configHandler->setParameter('cursus_disable_session_event_registration', $parameters['disableSessionEventRegistration']);
        $this->configHandler->setParameter('cursus_enable_courses_profile_tab', $parameters['enableCoursesProfileTab']);
        $this->configHandler->setParameter('cursus_enable_ws_in_courses_profile_tab', $parameters['enableWsInCoursesProfileTab']);
        $this->configHandler->setParameter('cursus_session_default_total', $parameters['sessionDefaultTotal']);
        $this->configHandler->setParameter('cursus_session_default_duration', $parameters['sessionDefaultDuration']);
        $this->configHandler->setParameter('cursus_display_user_events_in_desktop_agenda', $parameters['displayUserEventsInDesktopAgenda']);

        return new JsonResponse($parameters, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/document/models/retrieve",
     *     name="api_get_cursus_document_models",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns the document models list
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getDocumentModelsAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $models = $this->cursusManager->getAllDocumentModels();
        $serializedModels = $this->serializer->serialize(
            $models,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );

        return new JsonResponse($serializedModels, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/document/model/{documentModel}/retrieve",
     *     name="api_get_cursus_document_model",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns the document model
     *
     * @param User          $user
     * @param DocumentModel $documentModel
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getDocumentModelAction(User $user, DocumentModel $documentModel)
    {
        $this->cursusManager->checkAccess($user);
        $serializedModel = $this->serializer->serialize(
            $documentModel,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );

        return new JsonResponse($serializedModel, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/document/model/create",
     *     name="api_post_cursus_document_model_creation",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postDocumentModelCreateAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $documentModelDatas = $this->request->request->get('documentModelDatas', false);
        $documentModel = $this->cursusManager->createDocumentModel(
            $documentModelDatas['name'],
            $documentModelDatas['content'],
            $documentModelDatas['documentType']
        );
        $serializedDocumentModel = $this->serializer->serialize(
            $documentModel,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );

        return new JsonResponse($serializedDocumentModel, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/document/model/{documentModel}/edit",
     *     name="api_put_cursus_document_model_edition",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User          $user
     * @param DocumentModel $documentModel
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putDocumentModelEditAction(User $user, DocumentModel $documentModel)
    {
        $this->cursusManager->checkAccess($user);
        $documentModelDatas = $this->request->request->get('documentModelDatas', false);
        $documentModel->setName($documentModelDatas['name']);
        $documentModel->setContent($documentModelDatas['content']);
        $documentModel->setDocumentType($documentModelDatas['documentType']);
        $this->cursusManager->persistDocumentModel($documentModel);
        $serializedDocumentModel = $this->serializer->serialize(
            $documentModel,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );

        return new JsonResponse($serializedDocumentModel, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/document/model/{documentModel}/delete",
     *     name="api_delete_cursus_document_model",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Deletes session event
     *
     * @param User          $user
     * @param DocumentModel $documentModel
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteDocumentModelAction(User $user, DocumentModel $documentModel)
    {
        $this->cursusManager->checkAccess($user);
        $serializedDocumentModel = $this->serializer->serialize(
            $documentModel,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );
        $this->cursusManager->deleteDocumentModel($documentModel);

        return new JsonResponse($serializedDocumentModel, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/document/models/type/{type}/retrieve",
     *     name="api_get_cursus_document_models_by_type",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns the document models by type
     *
     * @param User $user
     * @param int  $type
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getDocumentModelsByTypeAction(User $user, $type)
    {
        $this->cursusManager->checkAccess($user);
        $documentModels = $this->cursusManager->getDocumentModelsByType($type);
        $serializedModels = $this->serializer->serialize(
            $documentModels,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );

        return new JsonResponse($serializedModels, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/populated/document/models/type/{type}/source/{sourceId}/retrieve",
     *     name="api_get_cursus_populated_document_models_by_type",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns the populated document models by type
     *
     * @param User $user
     * @param int  $type
     * @param int  $sourceId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getPopulatedDocumentModelsByTypeAction(User $user, $type, $sourceId)
    {
        $this->cursusManager->checkAccess($user);
        $documentModels = $this->cursusManager->getPopulatedDocumentModelsByType($type, $sourceId);

        return new JsonResponse($documentModels, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/populated/document/models/type/{type}/source/{sourceId}/for/user/{user}/retrieve",
     *     name="api_get_cursus_populated_document_models_by_type_for_user",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", converter="current_user")
     *
     * Returns the populated document models by type for an user
     *
     * @param User $authenticatedUser
     * @param User $user
     * @param int  $type
     * @param int  $sourceId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getPopulatedDocumentModelsByTypeForUserAction(User $authenticatedUser, User $user, $type, $sourceId)
    {
        $this->cursusManager->checkAccess($user);
        $documentModels = $this->cursusManager->getPopulatedDocumentModelsByType($type, $sourceId, $user);

        return new JsonResponse($documentModels, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/event/{sessionEvent}/repeat",
     *     name="api_post_session_event_repeat",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Repeats a session event
     *
     * @param User         $user
     * @param SessionEvent $sessionEvent
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postSessionEventRepeatAction(User $user, SessionEvent $sessionEvent)
    {
        $this->cursusManager->checkCourseAccess($user, $sessionEvent->getSession()->getCourse());
        $parameters = $this->request->request->get('repeatOptionsDatas', false);
        $iteration = [
            'Monday' => $parameters['monday'],
            'Tuesday' => $parameters['tuesday'],
            'Wednesday' => $parameters['wednesday'],
            'Thursday' => $parameters['thursday'],
            'Friday' => $parameters['friday'],
            'Saturday' => $parameters['saturday'],
            'Sunday' => $parameters['sunday'],
        ];
        $endDate = $parameters['endDate'] ? new \DateTime($parameters['endDate']) : null;
        $duration = $parameters['duration'];
        $createdSessionEvents = $this->cursusManager->repeatSessionEvent($sessionEvent, $iteration, $endDate, $duration);
        $serializedSessionEvents = $this->serializer->serialize(
            $createdSessionEvents,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedSessionEvents, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/location/create",
     *     name="api_post_cursus_location_creation",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postLocationCreateAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $locationDatas = $this->request->request->get('locationDatas', false);
        $location = new Location();
        $location->setType(Location::TYPE_TRAINING);
        $location->setName($locationDatas['name']);
        $location->setStreet($locationDatas['street']);
        $location->setStreetNumber($locationDatas['streetNumber']);
        $location->setBoxNumber($locationDatas['boxNumber']);
        $location->setPc($locationDatas['pc']);
        $location->setTown($locationDatas['town']);
        $location->setCountry($locationDatas['country']);
        $location->setPhone($locationDatas['phone']);
        $this->locationManager->create($location);
        $serializedLocation = $this->serializer->serialize(
            $location,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedLocation, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/location/{location}/edit",
     *     name="api_put_cursus_location_edition",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User     $user
     * @param Location $location
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putLocationEditAction(User $user, Location $location)
    {
        $this->cursusManager->checkAccess($user);

        if ($location->getType() !== Location::TYPE_TRAINING) {
            throw new AccessDeniedException();
        }
        $locationDatas = $this->request->request->get('locationDatas', false);
        $location->setName($locationDatas['name']);
        $location->setStreet($locationDatas['street']);
        $location->setStreetNumber($locationDatas['streetNumber']);
        $location->setBoxNumber($locationDatas['boxNumber']);
        $location->setPc($locationDatas['pc']);
        $location->setTown($locationDatas['town']);
        $location->setCountry($locationDatas['country']);
        $location->setPhone($locationDatas['phone']);
        $this->locationManager->edit($location);
        $serializedLocation = $this->serializer->serialize(
            $location,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedLocation, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/location/{location}/delete",
     *     name="api_delete_cursus_location",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Deletes session event
     *
     * @param User     $user
     * @param Location $location
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteLocationAction(User $user, Location $location)
    {
        $this->cursusManager->checkAccess($user);

        if ($location->getType() !== Location::TYPE_TRAINING) {
            throw new AccessDeniedException();
        }
        $serializedLocation = $this->serializer->serialize(
            $location,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );
        $this->locationManager->delete($location);

        return new JsonResponse($serializedLocation, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/message/send",
     *     name="api_post_session_message_send",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User          $user
     * @param CourseSession $session
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postSessionMessageSendAction(User $user, CourseSession $session)
    {
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $messageDatas = $this->request->request->get('messageDatas', false);
        $this->cursusManager->sendMessageToSession(
            $user,
            $session,
            $messageDatas['object'],
            $messageDatas['content'],
            $messageDatas['internal'],
            $messageDatas['external']
        );

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/document/model/{documentModel}/source/{sourceId}/send",
     *     name="api_post_cursus_document_send",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User          $user
     * @param DocumentModel $documentModel
     * @param int           $sourceId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postDocumentSendAction(User $user, DocumentModel $documentModel, $sourceId)
    {
        $this->cursusManager->checkAccess($user);
        $this->cursusManager->generateDocumentFromModel($documentModel, $sourceId);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/document/model/{documentModel}/source/{sourceId}/for/user/{user}/send",
     *     name="api_post_cursus_document_for_user_send",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", converter="current_user")
     *
     * @param User          $authenticatedUser
     * @param DocumentModel $documentModel
     * @param User          $user
     * @param int           $sourceId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postDocumentForUserSendAction(User $authenticatedUser, DocumentModel $documentModel, User $user, $sourceId)
    {
        $this->cursusManager->checkAccess($user);
        $this->cursusManager->generateDocumentFromModelForUser($documentModel, $user, $sourceId);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/api/session/{session}/users/type/{type}/csv/export",
     *     name="api_get_session_users_csv_export",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User          $user
     * @param CourseSession $session
     * @param int           $type
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function exportCsvSessionUsersAction(User $user, CourseSession $session, $type)
    {
        $this->cursusManager->checkCourseAccess($user, $session->getCourse());
        $exportType = intval($type);
        $users = [];

        if ($exportType === 1 || $exportType === 3) {
            $users['learners'] = $this->cursusManager->getUsersBySessionAndType($session, CourseSessionUser::LEARNER);
        }
        if ($exportType === 2 || $exportType === 3) {
            $users['trainers'] = $this->cursusManager->getUsersBySessionAndType($session, CourseSessionUser::TEACHER);
        }
        $response = new StreamedResponse();
        $response->setCallBack(
            function () use ($users) {
                $handle = fopen('php://output', 'r+');

                if (count($users) === 2) {
                    fwrite($handle, $this->translator->trans('trainers', [], 'cursus').PHP_EOL);
                }
                if (isset($users['trainers'])) {
                    foreach ($users['trainers'] as $user) {
                        fwrite($handle, implode(';', [$user->getFirstName(), $user->getLastName()]).PHP_EOL);
                    }
                }
                if (count($users) === 2) {
                    fwrite($handle, PHP_EOL);
                    fwrite($handle, $this->translator->trans('learners', [], 'cursus').PHP_EOL);
                }
                if (isset($users['learners'])) {
                    foreach ($users['learners'] as $user) {
                        fwrite($handle, implode(';', [$user->getFirstName(), $user->getLastName()]).PHP_EOL);
                    }
                }
                fclose($handle);
            }
        );
        $fileName = $session->getName();

        if ($exportType === 1) {
            $fileName .= '['.$this->translator->trans('learners', [], 'cursus').']';
        } elseif ($exportType === 2) {
            $fileName .= '['.$this->translator->trans('trainers', [], 'cursus').']';
        }

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$fileName.'.csv');
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/api/session/event/{sessionEvent}/users/type/{type}/csv/export",
     *     name="api_get_session_event_users_csv_export",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User         $user
     * @param SessionEvent $sessionEvent
     * @param int          $type
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsvSessionEventUsersAction(User $user, SessionEvent $sessionEvent, $type)
    {
        $this->cursusManager->checkCourseAccess($user, $sessionEvent->getSession()->getCourse());
        $exportType = intval($type);
        $users = [];

        if ($exportType === 1 || $exportType === 3) {
            $users['participants'] = $this->cursusManager->getUsersBySessionEventAndStatus($sessionEvent, SessionEventUser::REGISTERED);
        }
        if ($exportType === 2 || $exportType === 3) {
            $users['trainers'] = $sessionEvent->getTutors();
        }
        $response = new StreamedResponse();
        $response->setCallBack(
            function () use ($users) {
                $handle = fopen('php://output', 'r+');

                if (count($users) === 2) {
                    fwrite($handle, $this->translator->trans('trainers', [], 'cursus').PHP_EOL);
                }
                if (isset($users['trainers'])) {
                    foreach ($users['trainers'] as $user) {
                        fwrite($handle, implode(';', [$user->getFirstName(), $user->getLastName()]).PHP_EOL);
                    }
                }
                if (count($users) === 2) {
                    fwrite($handle, PHP_EOL);
                    fwrite($handle, $this->translator->trans('participants', [], 'cursus').PHP_EOL);
                }
                if (isset($users['participants'])) {
                    foreach ($users['participants'] as $user) {
                        fwrite($handle, implode(';', [$user->getFirstName(), $user->getLastName()]).PHP_EOL);
                    }
                }
                fclose($handle);
            }
        );
        $fileName = $sessionEvent->getName();

        if ($exportType === 1) {
            $fileName .= '['.$this->translator->trans('participants', [], 'cursus').']';
        } elseif ($exportType === 2) {
            $fileName .= '['.$this->translator->trans('trainers', [], 'cursus').']';
        }
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$fileName.'.csv');
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/all/root/cursus/retrieve",
     *     name="claroline_cursus_all_root_cursus_retrieve",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns all accessible root cursus for current user
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function allRootCursussRetrieveAction(User $user)
    {
        $this->cursusManager->checkAccess($user);

        if ($this->authorization->isGranted('ROLE_ADMIN')) {
            $cursus = $this->cursusManager->getAllRootCursus();
        } else {
            $organizations = $user->getAdministratedOrganizations()->toArray();
            $cursus = $this->cursusManager->getAllRootCursusByOrganizations($organizations);
        }
        $serializedCursus = $this->serializer->serialize(
            $cursus,
            'json',
            SerializationContext::create()->setGroups(['api_workspace_min'])
        );

        return new JsonResponse($serializedCursus, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/{cursus}/retrieve",
     *     name="claroline_cursus_retrieve",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns cursus
     *
     * @param User   $user
     * @param Cursus $cursus
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function cursusRetrieveAction(User $user, Cursus $cursus)
    {
        $this->cursusManager->checkCursusAccess($user, $cursus);
        $serializedCursus = $this->serializer->serialize(
            [$cursus],
            'json',
            SerializationContext::create()->setGroups(['api_workspace_min'])
        );

        return new JsonResponse($serializedCursus, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/all/courses/retrieve",
     *     name="claroline_cursus_all_courses_retrieve",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns all accessible courses for current user
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function allCoursesRetrieveAction(User $user)
    {
        $this->cursusManager->checkAccess($user);

        if ($this->authorization->isGranted('ROLE_ADMIN')) {
            $courses = $this->cursusManager->getAllCourses('', 'title', 'ASC', false);
        } else {
            $organizations = $user->getAdministratedOrganizations()->toArray();
            $courses = $this->cursusManager->getAllCoursesByOrganizations($organizations);
        }
        $serializedCourses = $this->serializer->serialize(
            $courses,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedCourses, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/all/sessions/retrieve",
     *     name="claroline_cursus_all_sessions_retrieve",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns all accessible sessions for current user
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function allSessionsRetrieveAction(User $user)
    {
        $this->cursusManager->checkAccess($user);

        if ($this->authorization->isGranted('ROLE_ADMIN')) {
            $sessions = $this->cursusManager->getAllSessions();
        } else {
            $organizations = $user->getAdministratedOrganizations()->toArray();
            $sessions = $this->cursusManager->getAllSessionsByOrganizations($organizations);
        }
        $serializedSessions = $this->serializer->serialize(
            $sessions,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedSessions, 200);
    }

    /**
     * @EXT\Route(
     *     "/cursus/organizations/retrieve",
     *     name="claro_cursus_organizations_retrieve",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Retrieves organizations for which user is administrator
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function cursusOrganizationsRetrieveAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $organizations = $this->authorization->isGranted('ROLE_ADMIN') ?
            $this->organizationManager->getAll() :
            $user->getAdministratedOrganizations()->toArray();
        $serializedOrganizations = $this->serializer->serialize(
            $organizations,
            'json',
            SerializationContext::create()->setGroups(['api_user'])
        );

        return new JsonResponse($serializedOrganizations, 200);
    }

    /**
     * @EXT\Route(
     *     "/cursus/{cursus}/unmapped/courses/retrieve",
     *     name="claroline_cursus_unmapped_courses_retrieve",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns all courses not linked to a curusus
     *
     * @param User   $user
     * @param Cursus $cursus
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAllUnmappedCoursesAction(User $user, Cursus $cursus)
    {
        $this->cursusManager->checkAccess($user);

        if ($this->authorization->isGranted('ROLE_ADMIN')) {
            $courses = $this->cursusManager->getUnmappedCoursesByCursus($cursus);
        } else {
            $organizations = $user->getAdministratedOrganizations()->toArray();
            $courses = $this->cursusManager->getUnmappedCoursesByCursusAndOrganizations($cursus, $organizations);
        }
        $serializedCourses = $this->serializer->serialize(
            $courses,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );

        return new JsonResponse($serializedCourses, 200);
    }

    /**
     * @EXT\Route(
     *     "/api/cursus/document/model/certificate/mail/retrieve",
     *     name="api_get_cursus_certificate_mail_document_model",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Returns the document model for the mail sent for certificate
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCertificateMailDocumentModelAction(User $user)
    {
        $this->cursusManager->checkAccess($user);
        $documentModel = $this->cursusManager->getCertificateEmail();
        $serializedModel = $this->serializer->serialize(
            $documentModel,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );

        return new JsonResponse($serializedModel, 200);
    }
}
