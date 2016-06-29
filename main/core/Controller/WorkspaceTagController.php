<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Symfony\Component\Form\FormFactory;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Form\WorkspaceTagType;
use Claroline\CoreBundle\Form\AdminWorkspaceTagType;

class WorkspaceTagController extends Controller
{
    private $tagManager;
    private $workspaceManager;
    private $tokenStorage;
    private $authorization;
    private $formFactory;
    private $utils;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "tagManager"         = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "authorization"      = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"       = @DI\Inject("security.token_storage"),
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "utils"              = @DI\Inject("claroline.security.utilities"),
     *     "translator"         = @DI\Inject("translator")
     * })
     */
    public function __construct(
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $tagManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        FormFactory $formFactory,
        Utilities $utils,
        TranslatorInterface $translator
    ) {
        $this->workspaceManager = $workspaceManager;
        $this->tagManager = $tagManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->formFactory = $formFactory;
        $this->utils = $utils;
        $this->translator = $translator;
    }

    /**
     * @EXT\Route(
     *     "admin/tag/add/children/{tagId}/{childrenString}",
     *     name="claro_admin_workspace_tag_add_children",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "tag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "tagId", "strictId" = true}
     * )
     *
     * Create hierarchy link between given admin tag and a given list of admin tags
     *
     * @param WorkspaceTag $tag
     * @param string       $childrenString
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAdminTagChildren(WorkspaceTag $tag, $childrenString)
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $children = explode(',', $childrenString);

        if (is_array($children) && count($children) > 0) {
            // Get all hierarchies where param $tag is a child
            $tagsHierarchy = $this->tagManager->getHierarchiesByUserAndTag($tag, null);
            // Get all hierarchies where parent is in param
            $childrenhierarchies = $this->tagManager->getAdminHierarchiesByParents($children);

            foreach ($childrenhierarchies as $childHierarchy) {
                foreach ($tagsHierarchy as $tagHierarchy) {
                    $this->tagManager->createTagHierarchy(
                        $childHierarchy->getTag(),
                        $tagHierarchy->getParent(),
                        $childHierarchy->getLevel() + $tagHierarchy->getLevel() + 1
                    );
                }
            }
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/tag/add/children/{tagId}/{childrenString}",
     *     name="claro_workspace_tag_add_children",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "tag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "tagId", "strictId" = true}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     *
     * Create hierarchy link between given tag and a given list of tags
     *
     * @param WorkspaceTag $tag
     * @param string       $childrenString
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addTagChildren(
        User $currentUser,
        WorkspaceTag $tag,
        $childrenString
    ) {
        $children = explode(',', $childrenString);

        if (is_array($children) && count($children) > 0) {
            // Get all hierarchies where param $tag is a child
            $tagsHierarchy = $this->tagManager
                ->getHierarchiesByUserAndTag($tag, $currentUser);
            // Get all hierarchies where parent is in param
            $childrenhierarchies = $this->tagManager
                ->getHierarchiesByParents($currentUser, $children);

            foreach ($childrenhierarchies as $childHierarchy) {
                foreach ($tagsHierarchy as $tagHierarchy) {
                    $this->tagManager->createTagHierarchy(
                        $childHierarchy->getTag(),
                        $tagHierarchy->getParent(),
                        $childHierarchy->getLevel() + $tagHierarchy->getLevel() + 1
                    );
                }
            }
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "admin/tag/create/form",
     *     name="claro_admin_workspace_tag_create_form",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template()
     *
     * Renders the Tag creation form
     *
     * @return Response
     */
    public function adminWorkspaceTagCreateFormAction()
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $workspaceTag = new WorkspaceTag();
        $form = $this->formFactory->create(new AdminWorkspaceTagType(), $workspaceTag);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "admin/tag/create",
     *     name="claro_admin_workspace_tag_create",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:WorkspaceTag:adminWorkspaceTagCreateForm.html.twig")
     *
     * Creates a new Tag
     *
     * @return RedirectResponse
     */
    public function adminWorkspaceTagCreateAction()
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $workspaceTag = new WorkspaceTag();

        $form = $this->formFactory->create(new AdminWorkspaceTagType(), $workspaceTag);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->tagManager->insert($workspaceTag);
            $this->tagManager->createTagHierarchy($workspaceTag, $workspaceTag, 0);

            return new Response($workspaceTag->getId(), 201);
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "tag/create/form",
     *     name="claro_workspace_tag_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     *
     * @EXT\Template()
     *
     * Renders the Tag creation form
     *
     * @return Response
     */
    public function workspaceTagCreateFormAction(User $currentUser)
    {
        $workspaceTag = new WorkspaceTag();
        $workspaceTag->setUser($currentUser);

        $form = $this->formFactory->create(new WorkspaceTagType(), $workspaceTag);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "tag/create",
     *     name="claro_workspace_tag_create",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     *
     * @EXT\Template("ClarolineCoreBundle:WorkspaceTag:workspaceTagCreateForm.html.twig")
     *
     * Creates a new Tag
     *
     * @return RedirectResponse
     */
    public function workspaceTagCreateAction(User $currentUser)
    {
        $workspaceTag = new WorkspaceTag();
        $workspaceTag->setUser($currentUser);
        $form = $this->formFactory->create(new WorkspaceTagType(), $workspaceTag);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->tagManager->insert($workspaceTag);
            $this->tagManager->createTagHierarchy($workspaceTag, $workspaceTag, 0);

            return new Response($workspaceTag->getId(), 201);
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "admin/tag/{workspaceTagId}/edit/form",
     *     name="claro_admin_workspace_tag_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "workspaceTag",
     *     class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *     options={"id" = "workspaceTagId", "strictId" = true}
     * )
     * @EXT\Template()
     *
     * Renders the Tag edition form
     *
     * @return Response
     */
    public function adminWorkspaceTagEditFormAction(WorkspaceTag $workspaceTag)
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $form = $this->formFactory->create(new AdminWorkspaceTagType(), $workspaceTag);

        return array(
            'form' => $form->createView(),
            'workspaceTag' => $workspaceTag,
        );
    }

    /**
     * @EXT\Route(
     *     "admin/tag/{workspaceTagId}/edit",
     *     name="claro_admin_workspace_tag_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "workspaceTag",
     *     class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *     options={"id" = "workspaceTagId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:WorkspaceTag:adminWorkspaceTagEditForm.html.twig")
     *
     * Edits name of a given tag
     */
    public function adminWorkspaceTagEditAction(WorkspaceTag $workspaceTag)
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $form = $this->formFactory->create(new AdminWorkspaceTagType(), $workspaceTag);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->tagManager->insert($workspaceTag);

            return new Response('success', 204);
        }

        return array(
            'form' => $form->createView(),
            'workspaceTag' => $workspaceTag,
        );
    }

    /**
     * @EXT\Route(
     *     "tag/{workspaceTagId}/edit/form",
     *     name="claro_workspace_tag_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "workspaceTag",
     *     class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *     options={"id" = "workspaceTagId", "strictId" = true}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Renders the Tag edition form
     *
     * @return Response
     */
    public function workspaceTagEditFormAction(
        User $currentUser,
        WorkspaceTag $workspaceTag
    ) {
        $form = $this->formFactory->create(new WorkspaceTagType(), $workspaceTag);

        return array(
            'form' => $form->createView(),
            'workspaceTag' => $workspaceTag,
        );
    }

    /**
     * @EXT\Route(
     *     "tag/{workspaceTagId}/edit",
     *     name="claro_workspace_tag_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "workspaceTag",
     *     class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *     options={"id" = "workspaceTagId", "strictId" = true}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:WorkspaceTag:workspaceTagEditForm.html.twig")
     *
     * Edits name of a given tag
     */
    public function workspaceTagEditAction(
        User $currentUser,
        WorkspaceTag $workspaceTag
    ) {
        $form = $this->formFactory->create(new WorkspaceTagType(), $workspaceTag);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->tagManager->insert($workspaceTag);

            return new Response('success', 204);
        }

        return array(
            'form' => $form->createView(),
            'workspaceTag' => $workspaceTag,
        );
    }

    /**
     * @EXT\Route(
     *     "admin/tag/{parentTagId}/remove/child/{childTagId}",
     *     name="claro_admin_workspace_tag_remove_child",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "parentTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "parentTagId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "childTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "childTagId", "strictId" = true}
     * )
     *
     * Removes hierarchy links between 2 admin tags
     *
     * @param WorkspaceTag $parentTag
     * @param WorkspaceTag $childTag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAdminTagHierarchy(WorkspaceTag $parentTag, WorkspaceTag $childTag)
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $childrenHierarchies = $this->tagManager
            ->getAdminHierarchiesByParent($childTag);
        // Get an array with all parents id
        $parentsTagsId = array();
        $parentsTags = $this->tagManager->getAdminParentsFromTag($parentTag);

        foreach ($parentsTags as $tagParent) {
            $parentsTagsId[] = $tagParent->getId();
        }
        // Get an array with all children id
        $childrenTagsId = array();
        $childrenTags = $this->tagManager->getAdminChildrenFromTag($childTag);

        foreach ($childrenTags as $childTag) {
            $childrenTagsId[] = $childTag->getId();
        }

        // Get all hierarchies where parents are in array $parentsTagsId and children in $childrenTagsId
        $multiHierarchies = $this->tagManager
            ->getAdminHierarchiesByParentsAndChildren(
                $parentsTagsId,
                $childrenTagsId
            );
        // Get all hierarchies where given tag (parent) is a child
        $parentHierarchies = $this->tagManager->getHierarchiesByTag($parentTag);
        $levelsArray = array();

        // Count the number of nodes by level
        foreach ($childrenHierarchies as $childHierarchy) {
            $childTagId = $childHierarchy->getTag()->getId();
            $level = $childHierarchy->getLevel() + 1;

            if (!isset($levelsArray[$childTagId])) {
                $levelsArray[$childTagId] = array();
            }
            if (!isset($levelsArray[$childTagId][$level])) {
                $levelsArray[$childTagId][$level] = 0;
            }
            ++$levelsArray[$childTagId][$level];
        }

        foreach ($parentHierarchies as $parentHierarchy) {
            $levelCount = $levelsArray;
            $parentWorkspaceTag = $parentHierarchy->getParent();
            $parentLevel = $parentHierarchy->getLevel();

            foreach ($multiHierarchies as $index => $singleHierarchy) {
                $currentTag = $singleHierarchy->getTag();
                $currentTagId = $currentTag->getId();
                $currentLevel = $singleHierarchy->getLevel();
                $currentParent = $singleHierarchy->getParent();

                $level = $currentLevel - $parentLevel;

                if ($currentParent === $parentWorkspaceTag &&
                    isset($levelCount[$currentTagId][$level]) &&
                    $levelCount[$currentTagId][$level] > 0) {
                    --$levelCount[$currentTagId][$level];
                    unset($multiHierarchies[$index]);
                    $this->tagManager->deleteTagHierarchy($singleHierarchy);
                }
            }
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "tag/{parentTagId}/remove/child/{childTagId}",
     *     name="claro_workspace_tag_remove_child",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "parentTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "parentTagId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "childTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "childTagId", "strictId" = true}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     *
     * Removes hierarchy links between 2 admin tags
     *
     * @param WorkspaceTag $parentTag
     * @param WorkspaceTag $childTag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeTagHierarchy(User $currentUser, WorkspaceTag $parentTag, WorkspaceTag $childTag)
    {
        $childrenHierarchies = $this->tagManager
            ->getHierarchiesByParent($currentUser, $childTag);
        // Get an array with all parents id
        $parentsTagsId = array();
        $parentsTags = $this->tagManager
            ->getParentsFromTag($currentUser, $parentTag);

        foreach ($parentsTags as $tagParent) {
            $parentsTagsId[] = $tagParent->getId();
        }
        // Get an array with all children id
        $childrenTagsId = array();
        $childrenTags = $this->tagManager
            ->getChildrenFromTag($currentUser, $childTag);

        foreach ($childrenTags as $childTag) {
            $childrenTagsId[] = $childTag->getId();
        }

        // Get all hierarchies where parents are in array $parentsTagsId and children in $childrenTagsId
        $multiHierarchies = $this->tagManager
            ->getHierarchiesByParentsAndChildren(
                $currentUser,
                $parentsTagsId,
                $childrenTagsId
            );
        // Get all hierarchies where given tag (parent) is a child
        $parentHierarchies = $this->tagManager->getHierarchiesByTag($parentTag);
        $levelsArray = array();

        // Count the number of nodes by level
        foreach ($childrenHierarchies as $childHierarchy) {
            $childTagId = $childHierarchy->getTag()->getId();
            $level = $childHierarchy->getLevel() + 1;

            if (!isset($levelsArray[$childTagId])) {
                $levelsArray[$childTagId] = array();
            }
            if (!isset($levelsArray[$childTagId][$level])) {
                $levelsArray[$childTagId][$level] = 0;
            }
            ++$levelsArray[$childTagId][$level];
        }

        foreach ($parentHierarchies as $parentHierarchy) {
            $levelCount = $levelsArray;
            $parentWorkspaceTag = $parentHierarchy->getParent();
            $parentLevel = $parentHierarchy->getLevel();

            foreach ($multiHierarchies as $index => $singleHierarchy) {
                $currentTag = $singleHierarchy->getTag();
                $currentTagId = $currentTag->getId();
                $currentLevel = $singleHierarchy->getLevel();
                $currentParent = $singleHierarchy->getParent();

                $level = $currentLevel - $parentLevel;

                if ($currentParent === $parentWorkspaceTag &&
                    isset($levelCount[$currentTagId][$level]) &&
                    $levelCount[$currentTagId][$level] > 0) {
                    --$levelCount[$currentTagId][$level];
                    unset($multiHierarchies[$index]);
                    $this->tagManager->deleteTagHierarchy($singleHierarchy);
                }
            }
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "admin/tag/{workspaceTagId}/check/children/page/{page}",
     *     name="claro_admin_workspace_tag_check_children_pager",
     *     defaults={"page"=1, "search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Route(
     *     "admin/tag/{workspaceTagId}/check/children/page/{page}/search/{search}",
     *     name="claro_admin_workspace_tag_check_children_pager_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "workspaceTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "workspaceTagId", "strictId" = true}
     * )
     *
     * @EXT\Template()
     *
     * Renders list of possible children for a given tag
     *
     * @param WorkspaceTag $tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkPotentialAdminWorkspaceTagChildrenPagerAction(
        WorkspaceTag $workspaceTag,
        $page,
        $search
    ) {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $possibleChildrenPager = $search === '' ?
            $this->tagManager
                ->getPossibleAdminChildrenPager($workspaceTag, $page) :
            $this->tagManager
                ->getPossibleAdminChildrenPagerBySearch($workspaceTag, $page, $search);

        return array(
            'workspaceTag' => $workspaceTag,
            'possibleChildren' => $possibleChildrenPager,
            'search' => $search,
        );
    }

    /**
     * @EXT\Route(
     *     "tag/{workspaceTagId}/check/children/page/{page}",
     *     name="claro_workspace_tag_check_children_pager",
     *     defaults={"page"=1, "search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Route(
     *     "tag/{workspaceTagId}/check/children/page/{page}/search/{search}",
     *     name="claro_workspace_tag_check_children_pager_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "workspaceTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "workspaceTagId", "strictId" = true}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     *
     * @EXT\Template()
     *
     * Renders list of possible children for a given tag
     *
     * @param WorkspaceTag $tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkPotentialWorkspaceTagChildrenPagerAction(
        User $currentUser,
        WorkspaceTag $workspaceTag,
        $page,
        $search
    ) {
        $possibleChildrenPager = $search === '' ?
            $this->tagManager
                ->getPossibleChildrenPager($currentUser, $workspaceTag, $page) :
            $this->tagManager->getPossibleChildrenPagerBySearch(
                $currentUser,
                $workspaceTag,
                $page,
                $search
            );

        return array(
            'workspaceTag' => $workspaceTag,
            'possibleChildren' => $possibleChildrenPager,
            'search' => $search,
        );
    }

    /**
     * @EXT\Route(
     *     "admin/tag/{workspaceTagId}/remove/workspace/{workspaceId}",
     *     name="claro_admin_workspace_tag_remove_from_workspace",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspaceTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "workspaceTagId", "strictId" = true}
     * )
     *
     * Remove admin Tag from Workspace
     *
     * @param Workspace workspace
     * @param WorkspaceTag $workspaceTag
     *
     * @return Response
     */
    public function removeAdminWorkspaceTagFromWorkspace(
        Workspace $workspace,
        WorkspaceTag $workspaceTag
    ) {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $relWorkspaceTag = $this->tagManager
            ->getAdminTagRelationByWorkspaceAndTag($workspace, $workspaceTag);
        $this->tagManager->deleteTagRelation($relWorkspaceTag);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "tag/{workspaceTagId}/remove/workspace/{workspaceId}",
     *     name="claro_workspace_tag_remove_from_workspace",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspaceTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "workspaceTagId", "strictId" = true}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     *
     * Remove Tag from Workspace
     *
     * @param Workspace workspace
     * @param WorkspaceTag $workspaceTag
     *
     * @return Response
     */
    public function removeWorkspaceTagFromWorkspace(
        User $currentUser,
        Workspace $workspace,
        WorkspaceTag $workspaceTag
    ) {
        $relWorkspaceTag = $this->tagManager->getTagRelationByWorkspaceAndTagAndUser(
            $workspace,
            $workspaceTag,
            $currentUser
        );

        if (!is_null($relWorkspaceTag)) {
            $this->tagManager->deleteTagRelation($relWorkspaceTag);
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/admin/workspace/tag/manage/page/{page}",
     *     name="claro_manage_admin_workspace_tag",
     *     defaults={"page"=1, "search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Route(
     *     "/admin/workspace/tag/manage/page/{page}/search/{search}",
     *     name="claro_manage_admin_workspace_tag_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template()
     *
     * Manage admin workspace tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageAdminWorkspaceTagAction($page, $search)
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $workspaces = ($search === '') ?
            $this->workspaceManager
                ->getDisplayableWorkspacesPager($page) :
            $this->workspaceManager
                ->getDisplayableWorkspacesBySearchPager($search, $page);
        $workspacesTags = array();

        foreach ($workspaces as $workspace) {
            $relWsTagsByWs = $this->tagManager
                ->getAdminTagRelationsByWorkspace($workspace);
            $workspacesTags[$workspace->getId()] = $relWsTagsByWs;
        }

        // Get datas to display tag hierarchy
        $tagsHierarchy = $this->tagManager->getAllAdminHierarchies();
        $rootTags = $this->tagManager->getAdminRootTags();
        $hierarchy = array();

        foreach ($tagsHierarchy as $tagHierarchy) {
            if ($tagHierarchy->getLevel() === 1) {
                if (!isset($hierarchy[$tagHierarchy->getParent()->getId()]) ||
                    !is_array($hierarchy[$tagHierarchy->getParent()->getId()])) {
                    $hierarchy[$tagHierarchy->getParent()->getId()] = array();
                }
                $hierarchy[$tagHierarchy->getParent()->getId()][] = $tagHierarchy->getTag();
            }
        }

        return array(
            'search' => $search,
            'workspaces' => $workspaces,
            'workspacesTags' => $workspacesTags,
            'hierarchy' => $hierarchy,
            'rootTags' => $rootTags,
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/tag/manage/page/{page}",
     *     name="claro_manage_workspace_tag",
     *     defaults={"page"=1, "search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Route(
     *     "/workspace/tag/manage/page/{page}/search/{search}",
     *     name="claro_manage_workspace_tag_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     *
     * @EXT\Template()
     *
     * Manage workspace tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageWorkspaceTagAction(User $currentUser, $page, $search)
    {
        $token = $this->tokenStorage->getToken();
        $roleNames = $this->utils->getRoles($token);
        $workspaces = ($search === '') ?
            $this->workspaceManager
                ->getOpenableWorkspacesByRolesPager($roleNames, $page, 20) :
            $this->workspaceManager
                ->getOpenableWorkspacesBySearchAndRolesPager($search, $roleNames, $page, 20);
        $workspacesTags = array();

        foreach ($workspaces as $workspace) {
            $relWsTagsByWs = $this->tagManager
                ->getAllTagRelationsByWorkspaceAndUser($workspace, $currentUser);
            $workspacesTags[$workspace->getId()] = $relWsTagsByWs;
        }

        // Get datas to display tag hierarchy
        $tagsHierarchy = $this->tagManager->getAllHierarchiesByUser($currentUser);
        $rootTags = $this->tagManager->getRootTags($currentUser);
        $hierarchy = array();

        foreach ($tagsHierarchy as $tagHierarchy) {
            if ($tagHierarchy->getLevel() === 1) {
                if (!isset($hierarchy[$tagHierarchy->getParent()->getId()]) ||
                    !is_array($hierarchy[$tagHierarchy->getParent()->getId()])) {
                    $hierarchy[$tagHierarchy->getParent()->getId()] = array();
                }
                $hierarchy[$tagHierarchy->getParent()->getId()][] = $tagHierarchy->getTag();
            }
        }

        return array(
            'user' => $currentUser,
            'search' => $search,
            'workspaces' => $workspaces,
            'workspacesTags' => $workspacesTags,
            'hierarchy' => $hierarchy,
            'rootTags' => $rootTags,
        );
    }

    /**
     * @EXT\Route(
     *     "associate/admin/tags/to/workspaces",
     *     name="claro_associate_admin_workspace_tags_to_workspaces",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true, "name" = "workspaceIds"}
     * )
     * @EXT\ParamConverter(
     *     "tags",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"multipleIds" = true, "name" = "tagIds"}
     * )
     */
    public function associateMultipleAdminTagsToMultipleWorkspacesAction(
        array $workspaces,
        array $tags
    ) {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        foreach ($workspaces as $workspace) {
            foreach ($tags as $tag) {
                $relWsTag = $this->tagManager
                    ->getAdminTagRelationByWorkspaceAndTag($workspace, $tag);

                if ($relWsTag === null) {
                    $this->tagManager->createTagRelation($tag, $workspace);
                    // Set success flashbag message
                    $msg = $this->translator->trans(
                        'the_workspace',
                        array(),
                        'platform'
                    );
                    $msg .= ' ['.$workspace->getName()
                        .' ('.$workspace->getCode().')] ';
                    $msg .= $this->translator->trans(
                        'has_been_put_in_category',
                        array(),
                        'platform'
                    );
                    $msg .= ' ['.$tag->getName().']';
                    $this->get('session')->getFlashBag()->add('success', $msg);
                } else {
                    // Set success flashbag message
                    $msg = $this->translator->trans(
                        'the_workspace',
                        array(),
                        'platform'
                    );
                    $msg .= ' ['.$workspace->getName()
                        .' ('.$workspace->getCode().')] ';
                    $msg .= $this->translator->trans(
                        'is_already_in_category',
                        array(),
                        'platform'
                    );
                    $msg .= ' ['.$tag->getName().']';
                    $this->get('session')->getFlashBag()->add('error', $msg);
                }
            }
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "associate/tags/to/workspaces",
     *     name="claro_associate_workspace_tags_to_workspaces",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true, "name" = "workspaceIds"}
     * )
     * @EXT\ParamConverter(
     *     "tags",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"multipleIds" = true, "name" = "tagIds"}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     */
    public function associateMultipleTagsToMultipleWorkspacesAction(
        User $currentUser,
        array $workspaces,
        array $tags
    ) {
        foreach ($workspaces as $workspace) {
            foreach ($tags as $tag) {
                $relWsTag = $this->tagManager->getTagRelationByWorkspaceAndTagAndUser(
                    $workspace,
                    $tag,
                    $currentUser
                );

                if ($relWsTag === null) {
                    $this->tagManager->createTagRelation($tag, $workspace);
                    // Set success flashbag message
                    $msg = $this->translator->trans(
                        'the_workspace',
                        array(),
                        'platform'
                    );
                    $msg .= ' ['.$workspace->getName()
                        .' ('.$workspace->getCode().')] ';
                    $msg .= $this->translator->trans(
                        'has_been_put_in_category',
                        array(),
                        'platform'
                    );
                    $msg .= ' ['.$tag->getName().']';
                    $this->get('session')->getFlashBag()->add('success', $msg);
                } else {
                    // Set success flashbag message
                    $msg = $this->translator->trans(
                        'the_workspace',
                        array(),
                        'platform'
                    );
                    $msg .= ' ['.$workspace->getName()
                        .' ('.$workspace->getCode().')] ';
                    $msg .= $this->translator->trans(
                        'is_already_in_category',
                        array(),
                        'platform'
                    );
                    $msg .= ' ['.$tag->getName().']';
                    $this->get('session')->getFlashBag()->add('error', $msg);
                }
            }
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "organize/admin/tags",
     *     name="claro_admin_workspace_tag_organize",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * Display list of admin workspace tags
     */
    public function organizeAdminWorkspaceTagAction()
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $tagsHierarchy = $this->tagManager->getAllAdminHierarchies();
        $rootTags = $this->tagManager->getAdminRootTags();
        $hierarchy = array();

        foreach ($tagsHierarchy as $tagHierarchy) {
            if ($tagHierarchy->getLevel() === 1) {
                if (!isset($hierarchy[$tagHierarchy->getParent()->getId()]) ||
                    !is_array($hierarchy[$tagHierarchy->getParent()->getId()])) {
                    $hierarchy[$tagHierarchy->getParent()->getId()] = array();
                }
                $hierarchy[$tagHierarchy->getParent()->getId()][] = $tagHierarchy->getTag();
            }
        }

        return array(
            'hierarchy' => $hierarchy,
            'rootTags' => $rootTags,
        );
    }

    /**
     * @EXT\Route(
     *     "organize/tags",
     *     name="claro_workspace_tag_organize",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Display list of workspace tags
     */
    public function organizeWorkspaceTagAction(User $currentUser)
    {
        $tagsHierarchy = $this->tagManager->getAllHierarchiesByUser($currentUser);
        $rootTags = $this->tagManager->getRootTags($currentUser);
        $hierarchy = array();

        foreach ($tagsHierarchy as $tagHierarchy) {
            if ($tagHierarchy->getLevel() === 1) {
                if (!isset($hierarchy[$tagHierarchy->getParent()->getId()]) ||
                    !is_array($hierarchy[$tagHierarchy->getParent()->getId()])) {
                    $hierarchy[$tagHierarchy->getParent()->getId()] = array();
                }
                $hierarchy[$tagHierarchy->getParent()->getId()][] = $tagHierarchy->getTag();
            }
        }

        return array(
            'hierarchy' => $hierarchy,
            'rootTags' => $rootTags,
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/tag/{workspaceTagId}/delete",
     *     name="claro_admin_workspace_tag_delete",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "workspaceTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "workspaceTagId", "strictId" = true}
     * )
     *
     * Delete admin Tag
     *
     * @param WorkspaceTag $workspaceTag
     *
     * @return Response
     */
    public function deleteAdminWorkspaceTag(WorkspaceTag $workspaceTag)
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        if (is_null($workspaceTag->getUser())) {
            $this->tagManager->deleteTag($workspaceTag);
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "tag/{workspaceTagId}/delete",
     *     name="claro_workspace_tag_delete",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "workspaceTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "workspaceTagId", "strictId" = true}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     *
     * Delete Tag
     *
     * @param WorkspaceTag $workspaceTag
     *
     * @return Response
     */
    public function deleteWorkspaceTag(User $currentUser, WorkspaceTag $workspaceTag)
    {
        if ($workspaceTag->getUser() === $currentUser) {
            $this->tagManager->deleteTag($workspaceTag);
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "admin/tag/{workspaceTagId}/link/workspace/{workspaceId}",
     *     name="claro_admin_workspace_tag_link_workspace",
     *     defaults={"workspaceId"=null},
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "workspaceTag",
     *     class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *     options={"id" = "workspaceTagId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Associate a Workspace Tag to a Workspace
     *
     * @return Response
     */
    public function adminWorkspaceTagLinkWorkspaceAction(
        WorkspaceTag $workspaceTag,
        Workspace $workspace = null
    ) {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        if (is_null($workspaceTag->getUser())) {
            $this->tagManager->linkWorkspace($workspaceTag, $workspace);
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{linkedWorkspaceId}/public/list/page/{page}",
     *     name="claro_render_public_workspace_list_pager",
     *     defaults={"page"=1, "search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Route(
     *     "/workspace/{linkedWorkspaceId}/public/list/page/{page}/search/{search}",
     *     name="claro_render_public_workspace_list_pager_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template()
     *
     * Get list of all public workspaces
     *
     * @return Response
     */
    public function renderPublicWorkspaceListPagerAction(
        $linkedWorkspaceId,
        $page,
        $search
    ) {
        $workspaces = ($search === '') ?
            $this->workspaceManager
                ->getDisplayableWorkspacesPager($page) :
            $this->workspaceManager
                ->getDisplayableWorkspacesBySearchPager($search, $page);

        return array(
            'linkedWorkspaceId' => $linkedWorkspaceId,
            'workspaces' => $workspaces,
            'search' => $search,
        );
    }
}
