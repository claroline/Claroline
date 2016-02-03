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

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusGroup;
use Claroline\CursusBundle\Entity\CursusUser;
use Claroline\CursusBundle\Entity\CourseSession;
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
     *     "cursusManager"   = @DI\Inject("claroline.manager.cursus_manager"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "requestStack"    = @DI\Inject("request_stack")
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
     * @View(serializerGroups={"api"})
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
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns datas for cursus registration",
     *     views = {"cursus"}
     * )
     */
    public function getDatasForCursusRegistrationAction(Cursus $cursus)
    {
        $hierarchy = array();
        $lockedHierarchy = array();
        $unlockedCursus = array();
        $hierarchyArray = array();
        $unlockedArray = array();
        $groupsArray = array();
        $usersArray = array();
        $allRelatedCursus = $this->cursusManager->getRelatedHierarchyByCursus($cursus);
        $cursusGroups = $this->cursusManager->getCursusGroupsByCursus($cursus);
        $cursusUsers = $this->cursusManager->getCursusUsersByCursus($cursus);

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

        foreach ($hierarchy as $key => $values) {
            $hierarchyArray[$key] = array();

            foreach ($values as $value) {
                $course = $value->getCourse();
                $valueEntry = array(
                    'id' => $value->getId(),
                    'code' => $value->getCode(),
                    'title' => $value->getTitle(),
                    'cursusOrder' => $value->getCursusOrder(),
                    'blocking' => $value->isBlocking(),
                    'description' => $value->getDescription(),
                    'details' => $value->getDetails(),
                    'lft' => $value->getLft(),
                    'rgt' => $value->getRgt(),
                    'lvl' => $value->getLvl(),
                    'root' => $value->getRoot(),
                    'course' => is_null($course) ? null : $course->getId()
                );
                $hierarchyArray[$key][] = $valueEntry;
            }
        }

        foreach ($unlockedCursus as $unlocked) {
            $unlockedArray[] = $unlocked->getId();
        }

        foreach ($cursusGroups as $cursusGroup) {
            $group = $cursusGroup->getGroup();
            $groupEntry = array(
                'id' => $cursusGroup->getId(),
                'groupType' => $cursusGroup->getGroupType(),
                'registrationDate' => $cursusGroup->getRegistrationDate(),
                'groupId' => $group->getId(),
                'groupName' => $group->getName(),
            );
            $groupsArray[] = $groupEntry;
        }

        foreach ($cursusUsers as $cursusUser) {
            $user = $cursusUser->getUser();
            $userEntry = array(
                'id' => $cursusUser->getId(),
                'userType' => $cursusUser->getUserType(),
                'registrationDate' => $cursusUser->getRegistrationDate(),
                'userId' => $user->getId(),
                'username' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName()
            );
            $usersArray[] = $userEntry;
        }

        return new JsonResponse(
            array(
                'hierarchy' => $hierarchyArray,
                'lockedHierarchy' => $lockedHierarchy,
                'unlockedCursus' => $unlockedArray,
                'cursusGroups' => $groupsArray,
                'cursusUsers' => $usersArray
            )
        );
    }

    /**
     * @View(serializerGroups={"api"})
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
     * @View(serializerGroups={"api"})
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
     * @View(serializerGroups={"api"})
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
     * @View(serializerGroups={"api"})
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
     * @View(serializerGroups={"api"})
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
     * @View(serializerGroups={"api"})
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


    /***********************************
     * Not used in angular refactoring *
     ***********************************/


    /**
     * @View(serializerGroups={"api"})
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
     * @View(serializerGroups={"api"})
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
     * @View(serializerGroups={"api"})
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
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the course list",
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
