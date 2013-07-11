<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class WorkspaceTagController extends Controller
{
    private $em;
    private $tagManager;
    private $workspaceManager;
    private $securityContext;
    private $formFactory;

    /**
     * @DI\InjectParams({
     *     "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "tagManager"         = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "securityContext"    = @DI\Inject("security.context"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory")
     * })
     */
    public function __construct(
        EntityManager $em,
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $tagManager,
        SecurityContextInterface $securityContext,
        FormFactory $formFactory
    )
    {
        $this->em = $em;
        $this->workspaceManager = $workspaceManager;
        $this->tagManager = $tagManager;
        $this->securityContext = $securityContext;
        $this->formFactory = $formFactory;
    }

    /**
     * @EXT\Route(
     *     "/tag",
     *     name="claro_workspace_manage_tag"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Display a table showing tags associated to user's workspaces
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageTagAction()
    {
        if (!$this->securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $user = $this->securityContext->getToken()->getUser();
        $tags = $this->tagManager->getTagsByUser($user);
        $workspaces = $this->workspaceManager->getWorkspacesByUser($user);
        $workspacesTags = array();

        foreach ($workspaces as $workspace) {
            $relWsTagsByWs = $this->tagManager->getAllTagRelationsByWorkspaceAndUser($workspace, $user);
            $workspacesTags[$workspace->getId()] = $relWsTagsByWs;
        }

        $tagsNameTxt = '[';
        foreach ($tags as $tag) {
            $tagsNameTxt .= '"' . $tag->getName() . '",';
        }
        $tagsNameTxt = substr($tagsNameTxt, 0, strlen($tagsNameTxt) - 1);
        $tagsNameTxt .= ']';

        return array(
            'user' => $user,
            'tagsNameTxt' => $tagsNameTxt,
            'workspaces' => $workspaces,
            'workspacesTags' => $workspacesTags
        );
    }

    /**
     * @EXT\Route(
     *     "/tag/admin",
     *     name="claro_workspace_manage_admin_tag"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Display a table showing tags associated to user's workspaces
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageAdminTagAction()
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $user = $this->securityContext->getToken()->getUser();
        $tags = $this->tagManager->getTagsByUser(null);
        $workspaces = $this->workspaceManager->getNonPersonalWorkspaces();
        $workspacesTags = array();

        foreach ($workspaces as $workspace) {
            $relWsTagsByWs = $this->tagManager->getAdminTagRelationsByWorkspace($workspace);
            $workspacesTags[$workspace->getId()] = $relWsTagsByWs;
        }

        $tagsNameTxt = '[';
        foreach ($tags as $tag) {
            $tagsNameTxt .= '"' . $tag->getName() . '",';
        }
        $tagsNameTxt = substr($tagsNameTxt, 0, strlen($tagsNameTxt) - 1);
        $tagsNameTxt .= ']';

        return array(
            'user' => $user,
            'tagsNameTxt' => $tagsNameTxt,
            'workspaces' => $workspaces,
            'workspacesTags' => $workspacesTags
        );
    }

    /**
     * @EXT\Route(
     *     "/tag/createform",
     *     name="claro_workspace_tag_create_form"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders the Tag creation form
     *
     * @return Response
     */
    public function workspaceTagCreateFormAction()
    {
        if (!$this->securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $user = $this->securityContext->getToken()->getUser();
        $workspaceTag = new WorkspaceTag();
        $workspaceTag->setUser($user);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_TAG, array(), $workspaceTag);

        return array(
            'form' => $form->createView(),
            'user' => $user
        );
    }

    /**
     * @EXT\Route(
     *     "/tag/admin/createform",
     *     name="claro_workspace_admin_tag_create_form"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders the Tag creation form
     *
     * @return Response
     */
    public function workspaceAdminTagCreateFormAction()
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $workspaceTag = new WorkspaceTag();
        $workspaceTag->setUser(null);
        $form = $this->formFactory->create(FormFactory::TYPE_ADMIN_WORKSPACE_TAG, array(), $workspaceTag);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/tag/create/{userId}",
     *     name="claro_workspace_tag_create"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:WorkspaceTag:workspaceTagCreateForm.html.twig")
     *
     * Creates a new Tag
     *
     * @param integer $userId
     *
     * @return RedirectResponse
     */
    public function workspaceTagCreateAction($userId)
    {
        if (!$this->securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $user = $this->securityContext->getToken()->getUser();

        if ($user->getId() != $userId) {
            throw new AccessDeniedException();
        }

        $workspaceTag = new WorkspaceTag();
        $workspaceTag->setUser($user);

        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_TAG, array(), $workspaceTag);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->tagManager->insert($workspaceTag);
            $this->tagManager->createTagHierarchy($workspaceTag, $workspaceTag, 0);

            return $this->redirect(
                $this->generateUrl('claro_workspace_manage_tag')
            );
        }

        return array(
            'form' => $form->createView(),
            'user' => $user
        );
    }

    /**
     * @EXT\Route(
     *     "/tag/admin/create",
     *     name="claro_workspace_admin_tag_create"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:WorkspaceTag:workspaceAdminTagCreateForm.html.twig")
     *
     * Creates a new Tag
     *
     * @return RedirectResponse
     */
    public function workspaceAdminTagCreateAction()
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $workspaceTag = new WorkspaceTag();
        $workspaceTag->setUser(null);

        $form = $this->formFactory->create(FormFactory::TYPE_ADMIN_WORKSPACE_TAG, array(), $workspaceTag);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->tagManager->insert($workspaceTag);
            $this->tagManager->createTagHierarchy($workspaceTag, $workspaceTag, 0);

            return $this->redirect(
                $this->generateUrl('claro_workspace_manage_admin_tag')
            );
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/{userId}/workspace/{workspaceId}/tag/add/{tagName}",
     *     name="claro_workspace_tag_add",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *      "targetUser",
     *      class="ClarolineCoreBundle:User",
     *      options={"id" = "userId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Adds a user tag to a workspace.
     *
     * @param User              $currentUser
     * @param User              $targetUser
     * @param AbstractWorkspace $workspaceId
     * @param string            $tagName
     *
     * @return Response
     */
    public function addTagToWorkspace(
        User $currentUser,
        User $targetUser,
        AbstractWorkspace $workspace,
        $tagName
    )
    {
        if ($currentUser !== $targetUser) {
            throw new AccessDeniedException();
        }

        $tag = $this->tagManager->getTagByNameAndUser($tagName, $targetUser);

        if ($tag === null) {
            $tag = $this->tagManager->createTag($tagName, $targetUser);
            $this->tagManager->createTagHierarchy($tag, $tag, 0);
        }
        $relWsTag = $this->tagManager->getTagRelationByWorkspaceAndTagAndUser(
            $workspace,
            $tag,
            $targetUser
        );

        if ($relWsTag === null) {
            $this->tagManager->createTagRelation($tag, $workspace);
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspaceId}/tag/add/{tagName}",
     *     name="claro_workspace_admin_tag_add",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Add Tag to Workspace
     *
     * @param AbstractWorkspace $workspace
     * @param string $tagName
     *
     * @return Response
     */
    public function addAdminTagToWorkspace(AbstractWorkspace $workspace, $tagName)
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        if (is_null($workspace)) {
            throw new \RuntimeException('Workspace cannot be null');
        }
        $tag = $this->tagManager->getTagByNameAndUser($tagName, null);

        if ($tag === null) {
            $tag = $this->tagManager->createTag($tagName);
            $this->tagManager->createTagHierarchy($tag, $tag, 0);
        }
        $relWsTag = $this->tagManager->getAdminTagRelationByWorkspaceAndTag($workspace, $tag);

        if ($relWsTag === null) {
            $this->tagManager->createTagRelation($tag, $workspace);
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/{userId}/workspace/{workspaceId}/tag/remove/{workspaceTagId}",
     *     name="claro_workspace_tag_remove",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "user",
     *      class="ClarolineCoreBundle:User",
     *      options={"id" = "userId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspaceTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "workspaceTagId", "strictId" = true}
     * )
     *
     * Remove Tag from Workspace
     *
     * @param User $userId
     * @param AbstractWorkspace $workspace
     * @param WorkspaceTag $workspaceTag
     *
     * @return Response
     */
    public function removeTagFromWorkspace(
        User $user,
        AbstractWorkspace $workspace,
        WorkspaceTag $workspaceTag
    )
    {
        $currentUser = $this->securityContext->getToken()->getUser();

        if (!$this->securityContext->isGranted('ROLE_USER')
            || $currentUser !== $workspaceTag->getUser()
            || $currentUser !== $user) {
            throw new AccessDeniedException();
        }
        $relWorkspaceTag = $this->tagManager
            ->getTagRelationByWorkspaceAndTagAndUser(
                $workspace,
                $workspaceTag,
                $user
            );
        $this->tagManager->deleteTagRelation($relWorkspaceTag);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspaceId}/tag/remove/{workspaceTagId}",
     *     name="claro_workspace_admin_tag_remove",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
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
     * @param AbstractWorkspace workspace
     * @param WorkspaceTag $workspaceTag
     *
     * @return Response
     */
    public function removeAdminTagFromWorkspace(
        AbstractWorkspace $workspace,
        WorkspaceTag $workspaceTag
    )
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        if (is_null($workspace) || is_null($workspaceTag)) {
            throw new \RuntimeException('Workspace or Tag cannot be null');
        }
        $relWorkspaceTag = $this->tagManager->getAdminTagRelationByWorkspaceAndTag($workspace, $workspaceTag);
        $this->tagManager->deleteTagRelation($relWorkspaceTag);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/tag/admin/organize",
     *     name="claro_workspace_organize_admin_tag"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Render a page where admin tags can be organized
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function organizeWorkspaceAdminTagAction()
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $tags = $this->tagManager->getTagsByUser(null);
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
            'tags' => $tags,
            'hierarchy' => $hierarchy,
            'rootTags' => $rootTags
        );
    }

    /**
     * @EXT\Route(
     *     "/tag/organize",
     *     name="claro_workspace_organize_tag"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Render a page where tags can be organized
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function organizeWorkspaceTagAction()
    {
        if (!$this->securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $user = $this->securityContext->getToken()->getUser();
        $tags = $this->tagManager->getTagsByUser($user);
        $tagsHierarchy = $this->tagManager->getAllHierarchiesByUser($user);
        $rootTags = $this->tagManager->getRootTags($user);
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
            'tags' => $tags,
            'hierarchy' => $hierarchy,
            'rootTags' => $rootTags
        );
    }

    /**
     * @EXT\Route(
     *     "/tag/admin/check/children/{tagId}",
     *     name="claro_workspace_admin_tag_check_children"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "tag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "tagId", "strictId" = true}
     * )
     *
     * @EXT\Template()
     *
     * Render a page where children can be added to a tag
     *
     * @param WorkspaceTag $tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkPotentialWorkspaceAdminTagChildrenAction(WorkspaceTag $tag)
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $children = $this->tagManager->getAdminChildren($tag);
        $possibleChildren = $this->tagManager->getPossibleAdminChildren($tag);

        return array(
            'tag' => $tag,
            'children' => $children,
            'possibleChildren' => $possibleChildren
        );
    }

    /**
     * @EXT\Route(
     *     "/tag/check/children/{tagId}",
     *     name="claro_workspace_tag_check_children"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "tag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "tagId", "strictId" = true}
     * )
     *
     * @EXT\Template()
     *
     * Render a page where children can be added to a tag
     *
     * @param WorkspaceTag $tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkPotentialWorkspaceTagChildrenAction(WorkspaceTag $tag)
    {
        if (!$this->securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $user = $this->securityContext->getToken()->getUser();
        $children = $this->tagManager->getChildren($user, $tag);
        $possibleChildren = $this->tagManager->getPossibleChildren($user, $tag);

        return array(
            'tag' => $tag,
            'children' => $children,
            'possibleChildren' => $possibleChildren
        );
    }

    /**
     * @EXT\Route(
     *     "/tag/admin/add/children/{tagId}/{childrenString}",
     *     name="claro_workspace_admin_tag_add_children",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "tag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "tagId", "strictId" = true}
     * )
     *
     * Create hierarchy link between given admin tag and a given list of admin tags
     *
     * @param WorkspaceTag $tag
     * @param string $childrenString
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAdminTagChildren(WorkspaceTag $tag, $childrenString)
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
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
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "tag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "tagId", "strictId" = true}
     * )
     *
     * Create hierarchy link between given tag and a given list of tags
     *
     * @param WorkspaceTag $tag
     * @param string $childrenString
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addTagChildren(WorkspaceTag $tag, $childrenString)
    {
        if (!$this->securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $user = $this->securityContext->getToken()->getUser();

        $children = explode(',', $childrenString);

        if (is_array($children) && count($children) > 0) {
            // Get all hierarchies where param $tag is a child
            $tagsHierarchy = $this->tagManager->getHierarchiesByUserAndTag($tag, $user);
            // Get all hierarchies where parent is in param
            $childrenhierarchies = $this->tagManager->getHierarchiesByParents($user, $children);

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
     *     "/tag/admin/remove/children/{tagId}/{childrenString}",
     *     name="claro_workspace_admin_tag_remove_children",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "tag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "tagId", "strictId" = true}
     * )
     *
     * Create hierarchy link between given admin tag and a given list of admin tags
     *
     * @param WorkspaceTag $tag
     * @param string $childrenString
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAdminTagChildren(WorkspaceTag $tag, $childrenString)
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $children = explode(',', $childrenString);

        if (is_array($children) && count($children) > 0) {
            // Get all hierarchies where parent is in param $childrenString
            $childrenHierarchy = $this->tagManager->getAdminHierarchiesByParents($children);
            // Get an array with all parents id
            $parentsTagsId = array();
            $parentsTags = $this->tagManager->getAdminParentsFromTag($tag);

            foreach ($parentsTags as $parentTag) {
                $parentsTagsId[] = $parentTag->getId();
            }
            // Get an array with all children id
            $childrenTagsId = array();
            $childrenTags = $this->tagManager->getAdminChildrenFromTags($children);

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
            $parentHierarchies = $this->tagManager->getHierarchiesByTag($tag);
            $levelsArray = array();

            // Count the number of nodes by level
            foreach ($childrenHierarchy as $childHierarchy) {
                $childTagId = $childHierarchy->getTag()->getId();
                $level = $childHierarchy->getLevel() + 1;

                if (!isset($levelsArray[$childTagId])) {
                    $levelsArray[$childTagId] = array();
                }
                if (!isset($levelsArray[$childTagId][$level])) {
                    $levelsArray[$childTagId][$level] = 0;
                }
                $levelsArray[$childTagId][$level]++;
            }

            foreach ($parentHierarchies as $parentHierarchy) {
                $levelCount = $levelsArray;
                $parentTag = $parentHierarchy->getParent();
                $parentLevel = $parentHierarchy->getLevel();

                foreach ($multiHierarchies as $index => $singleHierarchy) {
                    $currentTag = $singleHierarchy->getTag();
                    $currentTagId = $currentTag->getId();
                    $currentLevel = $singleHierarchy->getLevel();
                    $currentParent = $singleHierarchy->getParent();

                    $level = $currentLevel - $parentLevel;

                    if ($currentParent === $parentTag &&
                        isset($levelCount[$currentTagId][$level]) &&
                        $levelCount[$currentTagId][$level] > 0) {

                        $levelCount[$currentTagId][$level]--;
                        unset($multiHierarchies[$index]);
                        $this->tagManager->deleteTagHierarchy($singleHierarchy);
                    }
                }
            }
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/tag/remove/children/{tagId}/{childrenString}",
     *     name="claro_workspace_tag_remove_children",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "tag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "tagId", "strictId" = true}
     * )
     *
     * Create hierarchy link between given tag and a given list of tags
     *
     * @param integer $tagId
     * @param string $childrenString
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeTagChildren(WorkspaceTag $tag, $childrenString)
    {
        if (!$this->securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $user = $this->securityContext->getToken()->getUser();

        $children = explode(',', $childrenString);

        if (is_array($children) && count($children) > 0) {
            // Get all hierarchies where parent is in param $childrenString
            $childrenHierarchy = $this->tagManager->getHierarchiesByParents($user, $children);

            // Get an array with all parents id
            $parentsTagsId = array();
            $parentsTags = $this->tagManager->getParentsFromTag($user, $tag);

            foreach ($parentsTags as $parentTag) {
                $parentsTagsId[] = $parentTag->getId();
            }
            // Get an array with all children id
            $childrenTagsId = array();
            $childrenTags = $this->tagManager->getChildrenFromTags($user, $children);

            foreach ($childrenTags as $childTag) {
                $childrenTagsId[] = $childTag->getId();
            }
            // Get all hierarchies where parents are in array $parentsTagsId and children in $childrenTagsId
            $multiHierarchies = $this->tagManager
                ->getHierarchiesByParentsAndChildren($user, $parentsTagsId, $childrenTagsId);

            // Get all hierarchies where given tag (parent) is a child
            $parentHierarchies = $this->tagManager->getHierarchiesByUserAndTag($tag, $user);

            $levelsArray = array();

            // Count the number of nodes by level
            foreach ($childrenHierarchy as $childHierarchy) {
                $childTagId = $childHierarchy->getTag()->getId();
                $level = $childHierarchy->getLevel() + 1;

                if (!isset($levelsArray[$childTagId])) {
                    $levelsArray[$childTagId] = array();
                }
                if (!isset($levelsArray[$childTagId][$level])) {
                    $levelsArray[$childTagId][$level] = 0;
                }
                $levelsArray[$childTagId][$level]++;
            }

            foreach ($parentHierarchies as $parentHierarchy) {
                $levelCount = $levelsArray;
                $parentTag = $parentHierarchy->getParent();
                $parentLevel = $parentHierarchy->getLevel();

                foreach ($multiHierarchies as $index => $singleHierarchy) {
                    $currentTag = $singleHierarchy->getTag();
                    $currentTagId = $currentTag->getId();
                    $currentLevel = $singleHierarchy->getLevel();
                    $currentParent = $singleHierarchy->getParent();

                    $level = $currentLevel - $parentLevel;

                    if ($currentParent === $parentTag &&
                        isset($levelCount[$currentTagId][$level]) &&
                        $levelCount[$currentTagId][$level] > 0) {

                        $levelCount[$currentTagId][$level]--;
                        unset($multiHierarchies[$index]);
                        $this->tagManager->deleteTagHierarchy($singleHierarchy);
                    }
                }
            }
        }

        return new Response('success', 204);
    }
}