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
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusGroup;
use Claroline\CursusBundle\Entity\CursusUser;
use Claroline\CursusBundle\Manager\CursusManager;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @NamePrefix("api_")
 */
class CursusController extends FOSRestController
{
    private $cursusManager;

    /**
     * @DI\InjectParams({
     *     "cursusManager" = @DI\Inject("claroline.manager.cursus_manager")
     * })
     */
    public function __construct(CursusManager $cursusManager)
    {
        $this->cursusManager = $cursusManager;
    }

    /**
     * @View(serializerGroups={"api_cursus"})
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
     */
    public function getCursusUsersForCursusRegistrationAction(Cursus $cursus)
    {
        $datas = $this->cursusManager->getCursusUsersForCursusRegistration($cursus);

        return new JsonResponse($datas, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     */
    public function getDatasForSearchedCursusRegistrationAction($search)
    {
        $datas = $this->cursusManager->getDatasForSearchedCursusRegistration($search);

        return new JsonResponse($datas, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     */
    public function getDatasForCursusHierarchyAction(Cursus $cursus)
    {
        $datas = $this->cursusManager->getDatasForCursusHierarchy($cursus);

        return new JsonResponse($datas, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
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
     */
    public function deleteCursusGroupsAction($cursusGroupsIdsTxt)
    {
        $cursusGroups = $this->cursusManager->getCursusGroupsFromCursusGroupsIdsTxt($cursusGroupsIdsTxt);
        $this->cursusManager->unregisterGroupsFromCursus($cursusGroups);

        return new JsonResponse('success', 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
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
     */
    public function deleteCursusUsersAction(Cursus $cursus, $usersIdsTxt)
    {
        $users = $this->cursusManager->getUsersFromUsersIdsTxt($usersIdsTxt);
        $this->cursusManager->unregisterUsersFromCursus($cursus, $users);

        return new JsonResponse('success', 200);
    }

    /**
     * @View(serializerGroups={"api_group"})
     */
    public function getUnregisteredCursusGroupsAction(Cursus $cursus)
    {
        return ['groups' => $this->cursusManager->getUnregisteredGroupsByCursus($cursus)];
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     */
    public function getSearchedUnregisteredCursusGroupsAction(Cursus $cursus, $search)
    {
        return ['groups' => $this->cursusManager->getUnregisteredGroupsByCursus($cursus, $search)];
    }

    /**
     * @View(serializerGroups={"api_cursus"})
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
     */
    public function getSessionsForCursusListAction($cursusIdsTxt)
    {
        $cursusList = $this->cursusManager->getCursusFromCursusIdsTxt($cursusIdsTxt);
        $sessionsInfos = $this->cursusManager->getSessionsInfosFromCursusList($cursusList);

        return new JsonResponse($sessionsInfos, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     */
    public function postGroupRegisterToMultipleCursusAction(Group $group, $cursusIdsTxt, $sessionsIdsTxt)
    {
        $multipleCursus = $this->cursusManager->getCursusFromCursusIdsTxt($cursusIdsTxt);
        $sessions = $this->cursusManager->getSessionsFromSessionsIdsTxt($sessionsIdsTxt);
        $results = $this->cursusManager->registerGroupToCursusAndSessions($group, $multipleCursus, $sessions);

        return new JsonResponse($results, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     */
    public function postUsersRegisterToMultipleCursusAction($usersIdsTxt, $cursusIdsTxt, $sessionsIdsTxt)
    {
        $users = $this->cursusManager->getUsersFromUsersIdsTxt($usersIdsTxt);
        $multipleCursus = $this->cursusManager->getCursusFromCursusIdsTxt($cursusIdsTxt);
        $sessions = $this->cursusManager->getSessionsFromSessionsIdsTxt($sessionsIdsTxt);
        $results = $this->cursusManager->registerUsersToCursusAndSessions($users, $multipleCursus, $sessions);

        return new JsonResponse($results, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     */
    public function getRegistrationQueuesDatasAction()
    {
        $datas = $this->cursusManager->getRegistrationQueuesDatasByValidator();

        return $datas;
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     */
    public function getRegistrationQueuesDatasBySearchAction($search)
    {
        $datas = $this->cursusManager->getRegistrationQueuesDatasByValidator($search);

        return $datas;
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     */
    public function putCourseQueueValidateAction(CourseRegistrationQueue $queue)
    {
        $datas = $this->cursusManager->validateCourseQueue($queue);

        return new JsonResponse($datas, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
     */
    public function putSessionQueueValidateAction(CourseSessionRegistrationQueue $queue)
    {
        $datas = $this->cursusManager->validateSessionQueue($queue);

        return new JsonResponse($datas, 200);
    }

    /**
     * @View(serializerGroups={"api_cursus"})
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
     */
    public function postCourseQueuedUserTransferAction(CourseRegistrationQueue $queue, CourseSession $session)
    {
        $results = $this->cursusManager->transferQueuedUserToSession($queue, $session);

        return $results;
    }

    /**
     * @View(serializerGroups={"api_user_min"})
     * @Get("/course/{course}/sessions")
     */
    public function getSessionsByCourseAction(Course $course)
    {
        return $this->cursusManager->getSessionsByCourse($course, 'startDate', 'ASC');
    }

    /**
     * @View(serializerGroups={"api_user_min"})
     * @Get("/session/{session}/events")
     */
    public function getSessionEventsBySessionAction(CourseSession $session)
    {
        return $this->cursusManager->getEventsBySession($session, 'startDate', 'ASC');
    }
}
