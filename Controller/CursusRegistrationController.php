<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
use Claroline\CursusBundle\Entity\CursusGroup;
use Claroline\CursusBundle\Entity\CursusUser;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CursusRegistrationController extends Controller
{
    private $cursusManager;
    private $authorization;
    private $toolManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "cursusManager"   = @DI\Inject("claroline.manager.cursus_manager"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        CursusManager $cursusManager,
        AuthorizationCheckerInterface $authorization,
        ToolManager $toolManager,
        TranslatorInterface $translator
    )
    {
        $this->cursusManager = $cursusManager;
        $this->authorization = $authorization;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
    }

    /**
     * @EXT\Route(
     *     "/tool/registration/index",
     *     name="claro_cursus_tool_registration_index",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function cursusToolRegistrationIndexAction()
    {
        $this->checkToolAccess();
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }
        $hierarchy = array();
        $allCursus = $this->cursusManager->getAllCursus();

        foreach ($allCursus as $cursus) {
            $parent = $cursus->getParent();

            if (is_null($parent)) {

                if (!isset($hierarchy['root'])) {
                    $hierarchy['root'] = array();
                }
                $hierarchy['root'][] = $cursus;
            } else {
                $parentId = $parent->getId();

                if (!isset($hierarchy[$parentId])) {
                    $hierarchy[$parentId] = array();
                }
                $hierarchy[$parentId][] = $cursus;
            }
        }

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords,
            'allCursus' => $allCursus,
            'hierarchy' => $hierarchy
        );
    }

    /**
     * @EXT\Route(
     *     "/tool/registration/index/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_cursus_tool_registration_index_with_search",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="root","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function cursusToolRegistrationIndexWithSearchAction(
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'root',
        $order = 'ASC'
    )
    {
        $this->checkToolAccess();
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }

        $searchedCursus = $this->cursusManager->getAllCursus(
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );
        $rootIds = array();

        foreach ($searchedCursus as $cursus) {
            $root = $cursus->getRoot();

            if (!in_array($root, $rootIds)) {
                $rootIds[] = $root;
            }

        }
        $cursusRoots = $this->cursusManager->getCursusByIds($rootIds);
        $roots = array();

        foreach ($cursusRoots as $cursusRoot) {
            $roots[$cursusRoot->getId()] = $cursusRoot;
        }
        // To reduce DB queries only
        $courses = $this->cursusManager->getAllCourses('', 'id', 'ASC', false);

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords,
            'searchedCursus' => $searchedCursus,
            'search' => $search,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'page' => $page,
            'max' => $max,
            'roots' => $roots
        );
    }

    /**
     * @EXT\Route(
     *     "/cursus/{cursus}/registration/management",
     *     name="claro_cursus_registration_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function cursusRegistrationManagementAction(Cursus $cursus)
    {
        $this->checkToolAccess();
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }

        $hierarchy = array();
        $lockedHierarchy = array();
        $unlockedCursus = array();
        $allRelatedCursus = $this->cursusManager->getRelatedHierarchyByCursus($cursus);

        foreach ($allRelatedCursus as $oneCursus) {
            $parent = $oneCursus->getParent();
            $lockedHierarchy[$oneCursus->getId()] = 'blocked';

            if (is_null($parent)) {

                if (!isset($hierarchy['root'])) {
                    $hierarchy['root'] = array();
                }
                $hierarchy['root'][] = $oneCursus;
            } else {
                $parentId = $parent->getId();

                if (!isset($hierarchy[$parentId])) {
                    $hierarchy[$parentId] = array();
                }
                $hierarchy[$parentId][] = $oneCursus;
            }
        }
        $this->unlockedHierarchy($cursus, $hierarchy, $lockedHierarchy, $unlockedCursus);
        $unlockedIdsTemp = '';

        foreach ($unlockedCursus as $unlocked) {
            $unlockedIdsTemp .= $unlocked->getId() . ';';
        }
        $unlockedIdsTxt = trim($unlockedIdsTemp, ';');

        $cursusGroups = $this->cursusManager->getCursusGroupsByCursus($cursus);
        $cursusUsers = $this->cursusManager->getCursusUsersByCursus($cursus);

        // To reduce DB queries only
        $groups = $this->cursusManager->getGroupsByCursus($cursus);
        $users = $this->cursusManager->getUsersByCursus($cursus);

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords,
            'cursus' => $cursus,
            'cursusGroups' => $cursusGroups,
            'cursusUsers' => $cursusUsers,
            'hierarchy' => $hierarchy,
            'lockedHierarchy' => $lockedHierarchy,
            'unlockedCursus' => $unlockedCursus,
            'unlockedIdsTxt' => $unlockedIdsTxt
        );
    }

    /**
     * @EXT\Route(
     *     "/cursus/{cursus}/registration/unregistered/groups/list/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_cursus_registration_unregistered_groups_list",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="name","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Displays the list of groups who are not registered to the cursus.
     *
     * @param Cursus $cursus
     * @param string  $search
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     * @param string  $order
     */
    public function cursusRegistrationUnregisteredGroupsListAction(
        Cursus $cursus,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'name',
        $order = 'ASC'
    )
    {
        $this->checkToolAccess();

        $groups = $this->cursusManager->getUnregisteredGroupsByCursus(
            $cursus,
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );

        return array(
            'cursus' => $cursus,
            'groups' => $groups,
            'search' => $search,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "/cursus/{cursus}/registration/unregistered/users/list/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_cursus_registration_unregistered_users_list",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="firstName","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Displays the list of users who are not registered to the cursus.
     *
     * @param Cursus $cursus
     * @param string  $search
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     * @param string  $order
     */
    public function cursusRegistrationUnregisteredUsersListAction(
        Cursus $cursus,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'firstName',
        $order = 'ASC'
    )
    {
        $this->checkToolAccess();

        $users = $this->cursusManager->getUnregisteredUsersByCursus(
            $cursus,
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );

        return array(
            'cursus' => $cursus,
            'users' => $users,
            'search' => $search,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "multiple/cursus/register/user/{user}/confirm/sessions",
     *     name="claro_cursus_multiple_register_user_confirm_sessions",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "multipleCursus",
     *      class="ClarolineCursusBundle:Cursus",
     *      options={"multipleIds" = true, "name" = "cursusIds"}
     * )
     * @EXT\Template("ClarolineCursusBundle:CursusRegistration:registrationToMultipleCursusConfirmSession.html.twig")
     *
     * @param User $user
     * @param Cursus[] $multipleCursus
     */
    public function cursusUserRegisterToMultipleCursusConfirmSessionAction(
        User $user,
        array $multipleCursus
    )
    {
        $this->checkToolAccess();
        $courses = array();
        $sessionsList = array();

        foreach ($multipleCursus as $cursus) {
            $course = $cursus->getCourse();

            if (!is_null($course)) {
                $courses[] = $course;
            }
        }
        $sessions = $this->cursusManager->getSessionsByCourses($courses);

        foreach ($sessions as $session) {

            if ($session->getSessionStatus() !== 2) {
                $courseId = $session->getCourse()->getId();

                if (!isset($sessionsList[$courseId])) {
                    $sessionsList[$courseId] = array();
                }
                $sessionsList[$courseId][] = $session;
            }
        }

        return array(
            'user' => $user,
            'multipleCursus' => $multipleCursus,
            'courses' => $courses,
            'sessionsList' => $sessionsList,
            'type' => 'user'
        );
    }

    /**
     * @EXT\Route(
     *     "multiple/cursus/register/user/{user}",
     *     name="claro_cursus_multiple_register_user",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "multipleCursus",
     *      class="ClarolineCursusBundle:Cursus",
     *      options={"multipleIds" = true, "name" = "cursusIds"}
     * )
     * @EXT\ParamConverter(
     *     "sessions",
     *      class="ClarolineCursusBundle:CourseSession",
     *      options={"multipleIds" = true, "name" = "sessionIds"}
     * )
     *
     * @param User $user
     * @param Cursus[] $multipleCursus
     * @param CourseSession[] $sessions
     * @param User $authenticatedUser
     */
    public function cursusUserRegisterToMultipleCursusAction(
        User $user,
        array $multipleCursus,
        array $sessions,
        User $authenticatedUser
    )
    {
        $this->checkToolAccess();
        $coursesWithSession = array();
        $sessionsToCreate = array();
        $root = 0;
        $cursusRoot = null;
        $registrationDate = new \DateTime();

        foreach ($sessions as $session) {
            $course = $session->getCourse();
            $coursesWithSession[$course->getId()] = true;
        }

        foreach ($multipleCursus as $cursus) {
            $root = $cursus->getRoot();
            $course = $cursus->getCourse();

            if (!is_null($course) &&
                !isset($coursesWithSession[$course->getId()]) &&
                !in_array($course, $sessionsToCreate)) {

                $sessionsToCreate[] = $course;
            }
        }

        if ($root > 0) {
            $cursusRoot = $this->cursusManager->getOneCursusById($root);
            $this->cursusManager->associateCursusToSessions($cursusRoot, $sessions);
        }
        // Generate the list of sessions where the user will be register
        foreach ($sessionsToCreate as $course) {

            if (is_null($cursusRoot)) {
                $sessionName = 'Session';
            } else {
                $sessionName = $cursusRoot->getTitle();
            }
            $sessionName .= ' (' . $registrationDate->format('d/m/Y H:i') . ')';
            $session = $this->cursusManager->createCourseSession(
                $course,
                $authenticatedUser,
                $sessionName,
                $cursusRoot,
                $registrationDate
            );
            $sessions[] = $session;
        }
        $this->cursusManager->registerUserToMultipleCursus($multipleCursus, $user);
        $this->cursusManager->registerUsersToSessions($sessions, array($user));

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "multiple/cursus/register/users",
     *     name="claro_cursus_multiple_register_users",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "multipleCursus",
     *      class="ClarolineCursusBundle:Cursus",
     *      options={"multipleIds" = true, "name" = "cursusIds"}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "userIds"}
     * )
     *
     * @param Cursus $cursus
     * @param User[] $users
     */
    public function cursusUsersRegisterToMultipleCursusAction(
        array $multipleCursus,
        array $users
    )
    {
        $this->checkToolAccess();
        $this->cursusManager->registerUsersToMultipleCursus($multipleCursus, $users);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "multiple/cursus/register/group/{group}/confirm/sessions",
     *     name="claro_cursus_multiple_register_group_confirm_sessions",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "multipleCursus",
     *      class="ClarolineCursusBundle:Cursus",
     *      options={"multipleIds" = true, "name" = "cursusIds"}
     * )
     * @EXT\Template("ClarolineCursusBundle:CursusRegistration:registrationToMultipleCursusConfirmSession.html.twig")
     *
     * @param Group $group
     * @param Cursus[] $multipleCursus
     */
    public function cursusGroupRegisterToMultipleCursusConfirmSessionAction(
        Group $group,
        array $multipleCursus
    )
    {
        $this->checkToolAccess();
        $courses = array();
        $sessionsList = array();

        foreach ($multipleCursus as $cursus) {
            $course = $cursus->getCourse();

            if (!is_null($course)) {
                $courses[] = $course;
            }
        }
        $sessions = $this->cursusManager->getSessionsByCourses($courses);

        foreach ($sessions as $session) {

            if ($session->getSessionStatus() !== 2) {
                $courseId = $session->getCourse()->getId();

                if (!isset($sessionsList[$courseId])) {
                    $sessionsList[$courseId] = array();
                }
                $sessionsList[$courseId][] = $session;
            }
        }

        return array(
            'group' => $group,
            'multipleCursus' => $multipleCursus,
            'courses' => $courses,
            'sessionsList' => $sessionsList,
            'type' => 'group'
        );
    }

    /**
     * @EXT\Route(
     *     "multiple/cursus/register/group/{group}",
     *     name="claro_cursus_multiple_register_group",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "multipleCursus",
     *      class="ClarolineCursusBundle:Cursus",
     *      options={"multipleIds" = true, "name" = "cursusIds"}
     * )
     * @EXT\ParamConverter(
     *     "sessions",
     *      class="ClarolineCursusBundle:CourseSession",
     *      options={"multipleIds" = true, "name" = "sessionIds"}
     * )
     *
     * @param Group $group
     * @param Cursus[] $multipleCursus
     * @param CourseSession[] $sessions
     * @param User $authenticatedUser
     */
    public function cursusGroupRegisterToMultipleCursusAction(
        Group $group,
        array $multipleCursus,
        array $sessions,
        User $authenticatedUser
    )
    {
        $this->checkToolAccess();
        $coursesWithSession = array();
        $sessionsToCreate = array();
        $root = 0;
        $cursusRoot = null;
        $registrationDate = new \DateTime();

        foreach ($sessions as $session) {
            $course = $session->getCourse();
            $coursesWithSession[$course->getId()] = true;
        }

        foreach ($multipleCursus as $cursus) {
            $root = $cursus->getRoot();
            $course = $cursus->getCourse();

            if (!is_null($course) &&
                !isset($coursesWithSession[$course->getId()]) &&
                !in_array($course, $sessionsToCreate)) {

                $sessionsToCreate[] = $course;
            }
        }

        if ($root > 0) {
            $cursusRoot = $this->cursusManager->getOneCursusById($root);
        }
        // Generate the list of sessions where the user will be register
        foreach ($sessionsToCreate as $course) {
            $sessionName = $group->getName();
            $session = $this->cursusManager->createCourseSession(
                $course,
                $authenticatedUser,
                $sessionName,
                $cursusRoot,
                $registrationDate
            );
            $sessions[] = $session;
        }
        $this->cursusManager->registerGroupToMultipleCursus($multipleCursus, $group);
        $this->cursusManager->registerGroupToSessions($sessions, $group);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/user/{cursusUser}/delete",
     *     name="claro_cursus_user_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function cursusUserDeleteAction(CursusUser $cursusUser)
    {
        $this->checkToolAccess();
        $this->cursusManager->unregisterUserFromCursus(
            $cursusUser->getCursus(),
            $cursusUser->getUser()
        );

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/group/{cursusGroup}/delete",
     *     name="claro_cursus_group_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function cursusGroupDeleteAction(CursusGroup $cursusGroup)
    {
        $this->checkToolAccess();
        $this->cursusManager->unregisterGroupFromCursus(
            $cursusGroup->getCursus(),
            $cursusGroup->getGroup()
        );

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/view/related/hierarchy",
     *     name="claro_cursus_view_related_hierarchy",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param Cursus $cursus
     */
    public function cursusViewRelatedHierarchyAction(Cursus $cursus)
    {
        $this->checkToolAccess();
        $hierarchy = array();
        $allCursus = $this->cursusManager->getRelatedHierarchyByCursus($cursus);

        foreach ($allCursus as $oneCursus) {
            $parent = $oneCursus->getParent();

            if (is_null($parent)) {

                if (!isset($hierarchy['root'])) {
                    $hierarchy['root'] = array();
                }
                $hierarchy['root'][] = $oneCursus;
            } else {
                $parentId = $parent->getId();

                if (!isset($hierarchy[$parentId])) {
                    $hierarchy[$parentId] = array();
                }
                $hierarchy[$parentId][] = $oneCursus;
            }
        }

        return array('cursus' => $cursus, 'hierarchy' => $hierarchy);
    }

    private function unlockedHierarchy(
        Cursus $cursus,
        array $hierarchy,
        array &$lockedHierarchy,
        array &$unlockedCursus
    )
    {
        $lockedHierarchy[$cursus->getId()] = false;
        $unlockedCursus[] = $cursus;

        if (!$cursus->isBlocking()) {
            // Unlock parents
            $parent = $cursus->getParent();

            while (!is_null($parent) && !$parent->isBlocking()) {
                $lockedHierarchy[$parent->getId()] = 'up';
                $unlockedCursus[] = $parent;
                $parent = $parent->getParent();
            }
            // Unlock children
            $this->unlockedChildrenHierarchy(
                $cursus,
                $hierarchy,
                $lockedHierarchy,
                $unlockedCursus
            );
        }
    }

    private function unlockedChildrenHierarchy(
        Cursus $cursus,
        array $hierarchy,
        array &$lockedHierarchy,
        array &$unlockedCursus
    )
    {
        $cursusId = $cursus->getId();

        if (isset($hierarchy[$cursusId])) {

            foreach ($hierarchy[$cursusId] as $child) {

                if (!$child->isBlocking()) {
                    $lockedHierarchy[$child->getId()] = 'down';
                    $unlockedCursus[] = $child;
                    $this->unlockedChildrenHierarchy(
                        $child,
                        $hierarchy,
                        $lockedHierarchy,
                        $unlockedCursus
                    );
                }
            }
        }
    }

    private function checkToolAccess()
    {
        $cursusTool = $this->toolManager->getAdminToolByName('claroline_cursus_tool_registration');

        if (is_null($cursusTool) ||
            !$this->authorization->isGranted('OPEN', $cursusTool)) {

            throw new AccessDeniedException();
        }
    }
}
