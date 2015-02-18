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
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
use Claroline\CursusBundle\Entity\CursusGroup;
use Claroline\CursusBundle\Entity\CursusUser;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CursusRegistrationController extends Controller
{
    private $cursusManager;
    private $securityContext;
    private $toolManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "cursusManager"   = @DI\Inject("claroline.manager.cursus_manager"),
     *     "securityContext" = @DI\Inject("security.context"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        CursusManager $cursusManager,
        SecurityContextInterface $securityContext,
        ToolManager $toolManager,
        Translator $translator
    )
    {
        $this->cursusManager = $cursusManager;
        $this->securityContext = $securityContext;
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

        $searchedCursus = $this->cursusManager
            ->getSearchedCursus($search, $orderedBy, $order, $page, $max);
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
        $courses = $this->cursusManager->getAllCourses();

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

        $groups = $search === '' ?
            $this->cursusManager->getUnregisteredGroupsByCursus(
                $cursus,
                $orderedBy,
                $order,
                $page,
                $max
            ) :
            $this->cursusManager->getSearchedUnregisteredGroupsByCursus(
                $cursus,
                $search,
                $orderedBy,
                $order,
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

        $users = $search === '' ?
            $this->cursusManager->getUnregisteredUsersByCursus(
                $cursus,
                $orderedBy,
                $order,
                $page,
                $max
            ) :
            $this->cursusManager->getSearchedUnregisteredUsersByCursus(
                $cursus,
                $search,
                $orderedBy,
                $order,
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
     *     "cursus/{cursus}/register/user/{user}",
     *     name="claro_cursus_register_user",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function cursusUserRegisterAction(Cursus $cursus, User $user)
    {
        $this->checkToolAccess();
        $this->cursusManager->registerUserToCursus($cursus, $user);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "multiple/cursus/register/user/{user}",
     *     name="claro_cursus_multiple_register_user",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "multipleCursus",
     *      class="ClarolineCursusBundle:Cursus",
     *      options={"multipleIds" = true, "name" = "cursusIds"}
     * )
     *
     * @param User $user
     * @param Cursus[] $multipleCursus
     */
    public function cursusUserRegisterToMultipleCursusAction(
        User $user,
        array $multipleCursus
    )
    {
        $this->checkToolAccess();
        $this->cursusManager->registerUserToMultipleCursus($multipleCursus, $user);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/register/users",
     *     name="claro_cursus_register_users",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "userIds"}
     * )
     *
     * @param Cursus $cursus
     * @param User[] $users
     */
    public function cursusUsersRegisterAction(Cursus $cursus, array $users)
    {
        $this->checkToolAccess();
        $this->cursusManager->registerUsersToCursus($cursus, $users);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/unregister/user/{user}",
     *     name="claro_cursus_unregister_user",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function cursusUserUnregisterAction(Cursus $cursus, User $user)
    {
        $this->checkToolAccess();
        $this->cursusManager->unregisterUserFromCursus($cursus, $user);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/unregister/users",
     *     name="claro_cursus_unregister_users",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "userIds"}
     * )
     *
     * @param Cursus $cursus
     * @param User[] $users
     */
    public function cursusUsersUnregisterAction(Cursus $cursus, array $users)
    {
        $this->checkToolAccess();
        $this->cursusManager->unregisterUsersFromCursus($cursus, $users);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/register/group/{group}",
     *     name="claro_cursus_register_group",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function cursusGroupRegisterAction(Cursus $cursus, Group $group)
    {
        $this->checkToolAccess();
        $this->cursusManager->registerGroupToCursus($cursus, $group);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "multiple/cursus/register/group/{group}",
     *     name="claro_cursus_multiple_register_group",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "multipleCursus",
     *      class="ClarolineCursusBundle:Cursus",
     *      options={"multipleIds" = true, "name" = "cursusIds"}
     * )
     *
     * @param Group $group
     * @param Cursus[] $multipleCursus
     */
    public function cursusGroupRegisterToMultipleCursusAction(
        Group $group,
        array $multipleCursus
    )
    {
        $this->checkToolAccess();
        $this->cursusManager->registerGroupToMultipleCursus($multipleCursus, $group);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursus}/unregister/group/{group}",
     *     name="claro_cursus_unregister_group",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function cursusGroupUnregisterAction(Cursus $cursus, Group $group)
    {
        $this->checkToolAccess();
        $this->cursusManager->unregisterGroupFromCursus($cursus, $group);

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
            !$this->securityContext->isGranted('OPEN', $cursusTool)) {

            throw new AccessDeniedException();
        }
    }
}
