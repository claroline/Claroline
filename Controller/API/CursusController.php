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
use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusGroup;
use Claroline\CursusBundle\Entity\CursusUser;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSessionUser;
use Claroline\CursusBundle\Manager\CursusManager;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @NamePrefix("api_")
 */
class CursusController extends FOSRestController
{
    private $cursusManager;
    private $formFactory;
    private $request;

    /**
     * @DI\InjectParams({
     *     "cursusManager" = @DI\Inject("claroline.manager.cursus_manager"),
     *     "formFactory"   = @DI\Inject("form.factory"),
     *     "requestStack"  = @DI\Inject("request_stack")
     * })
     */
    public function __construct(
        CursusManager $cursusManager,
        FormFactory $formFactory,
        RequestStack $requestStack
    )
    {
        $this->cursusManager = $cursusManager;
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Returns root cursus list",
     *     views = {"cursus"}
     * )
     */
    public function getAllRootCursusAction()
    {
        return $this->cursusManager->getAllRootCursus('', 'cursusOrder');
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Returns datas for cursus registration",
     *     views = {"cursus"}
     * )
     */
    public function getDatasForCursusRegistrationAction(Cursus $cursus)
    {
        $datas = $this->cursusManager->getCursusDatasForCursusRegistration($cursus);
        $cursusGroupsDatas = $this->cursusManager->getCursusGroupsForCursusRegistration($cursus);
        $cursusUsersDatas = $this->cursusManager->getCursusUsersForCursusRegistration($cursus);
        $datas['cursusGroups'] = $cursusGroupsDatas;
        $datas['cursusUsers'] = $cursusUsersDatas;

        return new JsonResponse($datas, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Returns cursus users for cursus registration",
     *     views = {"cursus"}
     * )
     */
    public function getCursusUsersForCursusRegistrationAction(Cursus $cursus)
    {
        $datas = $this->cursusManager->getCursusUsersForCursusRegistration($cursus);

        return new JsonResponse($datas, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Returns datas for searched cursus",
     *     views = {"cursus"}
     * )
     */
    public function getDatasForSearchedCursusRegistrationAction($search)
    {
        $datas = $this->cursusManager->getDatasForSearchedCursusRegistration($search);

        return new JsonResponse($datas, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Returns hierarchy datas for cursus",
     *     views = {"cursus"}
     * )
     */
    public function getDatasForCursusHierarchyAction(Cursus $cursus)
    {
        $datas = $this->cursusManager->getDatasForCursusHierarchy($cursus);

        return new JsonResponse($datas, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Unregister group from cursus",
     *     views = {"cursus"}
     * )
     */
    public function deleteCursusGroupAction(CursusGroup $cursusGroup)
    {
        $this->cursusManager->unregisterGroupFromCursus(
            $cursusGroup->getCursus(),
            $cursusGroup->getGroup()
        );

        return new JsonResponse('success', 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Unregister groups from cursus",
     *     views = {"cursus"}
     * )
     */
    public function deleteCursusGroupsAction($cursusGroupsIdsTxt)
    {
        $cursusGroups = $this->cursusManager
            ->getCursusGroupsFromCursusGroupsIdsTxt($cursusGroupsIdsTxt);
        $this->cursusManager->unregisterGroupsFromCursus($cursusGroups);

        return new JsonResponse('success', 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Unregister user from cursus",
     *     views = {"cursus"}
     * )
     */
    public function deleteCursusUserAction(CursusUser $cursusUser)
    {
        $this->cursusManager->unregisterUserFromCursus(
            $cursusUser->getCursus(),
            $cursusUser->getUser()
        );

        return new JsonResponse('success', 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Unregister users from cursus",
     *     views = {"cursus"}
     * )
     */
    public function deleteCursusUsersAction(Cursus $cursus, $usersIdsTxt)
    {
        $users = $this->cursusManager->getUsersFromUsersIdsTxt($usersIdsTxt);
        $this->cursusManager->unregisterUsersFromCursus($cursus, $users);

        return new JsonResponse('success', 200);
    }

    /**
     * @View(serializerGroups={"api_group"})
     * @ApiDoc(
     *     description="Retrieve groups that are not registered to cursus",
     *     views = {"cursus"}
     * )
     */
    public function getUnregisteredCursusGroupsAction(Cursus $cursus)
    {
        return $this->cursusManager->getUnregisteredGroupsByCursus(
            $cursus,
            '',
            'name',
            'ASC',
            false
        );
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Retrieve searched groups that are not registered to cursus",
     *     views = {"cursus"}
     * )
     */
    public function getSearchedUnregisteredCursusGroupsAction(Cursus $cursus, $search)
    {
        return $this->cursusManager->getUnregisteredGroupsByCursus(
            $cursus,
            $search,
            'name',
            'ASC',
            false
        );
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Retrieve users who are not registered to cursus",
     *     views = {"cursus"}
     * )
     */
    public function getUnregisteredCursusUsersAction(Cursus $cursus)
    {
        return $this->cursusManager->getUnregisteredUsersByCursus(
            $cursus,
            '',
            'lastName',
            'ASC',
            false
        );
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Retrieve searched users who are not registered to cursus",
     *     views = {"cursus"}
     * )
     */
    public function getSearchedUnregisteredCursusUsersAction(Cursus $cursus, $search = '')
    {
        return $this->cursusManager->getUnregisteredUsersByCursus(
            $cursus,
            $search,
            'lastName',
            'ASC',
            false
        );
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Retrieve sessions infos from a list of cursus",
     *     views = {"cursus"}
     * )
     */
    public function getSessionsForCursusListAction($cursusIdsTxt)
    {
        $cursusList = $this->cursusManager->getCursusFromCursusIdsTxt($cursusIdsTxt);
        $sessionsInfos = $this->cursusManager->getSessionsInfosFromCursusList($cursusList);

        return new JsonResponse($sessionsInfos, 200);
    }
    
    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Register group to cursus and sessions",
     *     views = {"cursus"}
     * )
     */
    public function postGroupRegisterToMultipleCursusAction(
        Group $group,
        $cursusIdsTxt,
        $sessionsIdsTxt
    )
    {
        $multipleCursus = $this->cursusManager->getCursusFromCursusIdsTxt($cursusIdsTxt);
        $sessions = $this->cursusManager->getSessionsFromSessionsIdsTxt($sessionsIdsTxt);
        $results = $this->cursusManager->registerGroupToCursusAndSessions($group, $multipleCursus, $sessions);

        return new JsonResponse($results, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Register array of users to cursus and sessions",
     *     views = {"cursus"}
     * )
     */
    public function postUsersRegisterToMultipleCursusAction(
        $usersIdsTxt,
        $cursusIdsTxt,
        $sessionsIdsTxt
    )
    {
        $users = $this->cursusManager->getUsersFromUsersIdsTxt($usersIdsTxt);
        $multipleCursus = $this->cursusManager->getCursusFromCursusIdsTxt($cursusIdsTxt);
        $sessions = $this->cursusManager->getSessionsFromSessionsIdsTxt($sessionsIdsTxt);
        $results = $this->cursusManager->registerUsersToCursusAndSessions($users, $multipleCursus, $sessions);

        return new JsonResponse($results, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Returns datas for registration queues",
     *     views = {"cursus"}
     * )
     */
    public function getRegistrationQueuesDatasAction()
    {
        $datas = $this->cursusManager->getRegistrationQueuesDatasByValidator();

        return $datas;
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Returns datas for registration queues",
     *     views = {"cursus"}
     * )
     */
    public function getRegistrationQueuesDatasBySearchAction($search)
    {
        $datas = $this->cursusManager->getRegistrationQueuesDatasByValidator($search);

        return $datas;
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Validate session queue",
     *     views = {"cursus"}
     * )
     */
    public function putCourseQueueValidateAction(CourseRegistrationQueue $queue)
    {
        $datas = $this->cursusManager->validateCourseQueue($queue);

        return new JsonResponse($datas, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Validate session queue",
     *     views = {"cursus"}
     * )
     */
    public function putSessionQueueValidateAction(CourseSessionRegistrationQueue $queue)
    {
        $datas = $this->cursusManager->validateSessionQueue($queue);

        return new JsonResponse($datas, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Delete session queue",
     *     views = {"cursus"}
     * )
     */
    public function deleteCourseQueueAction(CourseRegistrationQueue $queue)
    {
        $canValidate = $this->cursusManager->canValidateCourseQueue($queue);

        if (!$canValidate) {

            return new JsonResponse('not_authorized', 403);
        }
        $queueDatas = $this->cursusManager->declineCourseQueue($queue);

        return new JsonResponse($queueDatas, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Delete session queue",
     *     views = {"cursus"}
     * )
     */
    public function deleteSessionQueueAction(CourseSessionRegistrationQueue $queue)
    {
        $canValidate = $this->cursusManager->canValidateSessionQueue($queue);

        if (!$canValidate) {

            return new JsonResponse('not_authorized', 403);
        }
        $queueDatas = $this->cursusManager->declineSessionQueue($queue);

        return new JsonResponse($queueDatas, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Get all unclosed sessions of a course",
     *     views = {"cursus"}
     * )
     */
    public function getAvailableSessionsByCourseAction(Course $course)
    {
        $notStartedsessions = $this->cursusManager->getSessionsByCourseAndStatus(
            $course,
            CourseSession::SESSION_NOT_STARTED
        );
        $openSessions = $this->cursusManager->getSessionsByCourseAndStatus(
            $course,
            CourseSession::SESSION_OPEN
        );
        $sessions = array_merge($notStartedsessions, $openSessions);

        return $sessions;
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Registers user from course queue to a session",
     *     views = {"cursus"}
     * )
     */
    public function postCourseQueuedUserTransferAction(
        CourseRegistrationQueue $queue,
        CourseSession $session
    )
    {
        $results = $this->cursusManager->transferQueuedUserToSession($queue, $session);

        return $results;
    }

    /***********************************
     * Not used in angular refactoring *
     ***********************************/


    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Returns all the cursus list",
     *     views = {"cursus"}
     * )
     */
    public function getAllCursusAction()
    {
        return $this->cursusManager->getAllCursus();
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Returns all the cursus list order by parent",
     *     views = {"cursus"}
     * )
     */
    public function getAllCursusHierarchyAction()
    {
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
        return $hierarchy;
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Returns the cursus list",
     *     views = {"cursus"}
     * )
     */
    public function getCursusAction(Cursus $cursus)
    {
        return $this->cursusManager->getHierarchyByCursus($cursus);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     * @ApiDoc(
     *     description="Returns the courses list",
     *     views = {"cursus"}
     * )
     */
    public function getCourseAction()
    {
        return $this->cursusManager->getAllCourses('', 'title', 'ASC', false);
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Register an user to a cursus",
     *     views = {"cursus"}
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function addUserToCursusAction(User $user, Cursus $cursus)
    {
        $this->cursusManager->registerUserToCursus($cursus, $user);

        return array('success');
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Unregister an user from a cursus",
     *     views = {"cursus"}
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function removeUserFromCursusAction(User $user, Cursus $cursus)
    {
        $this->cursusManager->unregisterUserFromCursus($cursus, $user);

        return array('success');
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Register an user to a course session",
     *     views = {"cursus"}
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function addUserToSessionAction(User $user, CourseSession $session, $type = 0)
    {
        $this->cursusManager->registerUsersToSession($session, array($user), $type);

        return array('success');
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Unregister an user from a course session",
     *     views = {"cursus"}
     * )
     */
    public function removeUserFromSessionAction(CourseSessionUser $sessionUser)
    {
        $this->cursusManager->unregisterUsersFromSession(array($sessionUser));

        return array('success');
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Register an user to a cursus hierarchy",
     *     views = {"cursus"}
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function addUserToCursusHierarchyAction(User $user, Cursus $cursus)
    {
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
        $this->cursusManager->unlockedHierarchy(
            $cursus,
            $hierarchy,
            $lockedHierarchy,
            $unlockedCursus
        );
        $this->cursusManager->registerUserToMultipleCursus($unlockedCursus, $user, true, true);

        return array('success');
    }
}
