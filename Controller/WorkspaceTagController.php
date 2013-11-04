<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\Translator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class WorkspaceTagController extends Controller
{
    private $tagManager;
    private $workspaceManager;
    private $securityContext;
    private $formFactory;
    private $utils;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "tagManager"         = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "securityContext"    = @DI\Inject("security.context"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "utils"              = @DI\Inject("claroline.security.utilities"),
     *     "translator"         = @DI\Inject("translator")
     * })
     */
    public function __construct(
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $tagManager,
        SecurityContextInterface $securityContext,
        FormFactory $formFactory,
        Utilities $utils,
        Translator $translator
    )
    {
        $this->workspaceManager = $workspaceManager;
        $this->tagManager = $tagManager;
        $this->securityContext = $securityContext;
        $this->formFactory = $formFactory;
        $this->utils = $utils;
        $this->translator = $translator;
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
        $token = $this->securityContext->getToken();
        $roles = $this->utils->getRoles($token);
        $workspaces = $this->workspaceManager->getWorkspacesByRoles($roles);
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

        $form = $this->formFactory->create(
            FormFactory::TYPE_ADMIN_WORKSPACE_TAG,
            array(),
            $workspaceTag
        );
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
     *     "create/tag/{tagName}",
     *     name="claro_create_workspace_tag",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     *
     * Create a workspace tag.
     *
     * @param User              $currentUser
     * @param string            $tagName
     *
     * @return Response
     */
    public function createWorkspaceTag(User $currentUser, $tagName)
    {
        $tag = $this->tagManager->getTagByNameAndUser($tagName, $currentUser);

        if ($tag === null) {
            $tag = $this->tagManager->createTag($tagName, $currentUser);
            $this->tagManager->createTagHierarchy($tag, $tag, 0);
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "create/admin/tag/{tagName}",
     *     name="claro_create_admin_workspace_tag",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     *
     * Create an admin workspace tag.
     *
     * @param string            $tagName
     *
     * @return Response
     */
    public function createAdminWorkspaceTag($tagName)
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $tag = $this->tagManager->getTagByNameAndUser($tagName, null);

        if ($tag === null) {
            $tag = $this->tagManager->createTag($tagName);
            $this->tagManager->createTagHierarchy($tag, $tag, 0);
        }

        return new Response('success', 204);
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
     * @param string            $tagName
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
     * @param User              $userId
     * @param AbstractWorkspace $workspace
     * @param WorkspaceTag      $workspaceTag
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
     * @param string       $childrenString
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
     * @param string       $childrenString
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
     * @param string  $childrenString
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

    /**
     * @EXT\Route(
     *     "admin/tag/create/form",
     *     name="claro_admin_workspace_tag_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders the Tag creation form
     *
     * @return Response
     */
    public function adminWorkspaceTagCreateFormAction()
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $workspaceTag = new WorkspaceTag();
        $form = $this->formFactory->create(
            FormFactory::TYPE_ADMIN_WORKSPACE_TAG,
            array(),
            $workspaceTag
        );

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
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $workspaceTag = new WorkspaceTag();

        $form = $this->formFactory->create(
            FormFactory::TYPE_ADMIN_WORKSPACE_TAG,
            array(),
            $workspaceTag
        );
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
     * @EXT\Method("GET")
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
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $form = $this->formFactory->create(
            FormFactory::TYPE_ADMIN_WORKSPACE_TAG,
            array(),
            $workspaceTag
        );

        return array(
            'form' => $form->createView(),
            'workspaceTag' => $workspaceTag
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
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $form = $this->formFactory->create(
            FormFactory::TYPE_ADMIN_WORKSPACE_TAG,
            array(),
            $workspaceTag
        );
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->tagManager->insert($workspaceTag);

            return new Response('success', 204);
        }

        return array(
            'form' => $form->createView(),
            'workspaceTag' => $workspaceTag
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
    public function removeAdminTagHierarchy(
        WorkspaceTag $parentTag,
        WorkspaceTag $childTag
    )
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
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
            $levelsArray[$childTagId][$level]++;
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

                    $levelCount[$currentTagId][$level]--;
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
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "admin/tag/{workspaceTagId}/check/children/page/{page}/search/{search}",
     *     name="claro_admin_workspace_tag_check_children_pager_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
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
    )
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
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
            'search' => $search
        );
    }

    /**
     * @EXT\Route(
     *     "admin/tag/{workspaceTagId}/check/workspace/page/{page}",
     *     name="claro_admin_workspace_tag_check_workspace_pager",
     *     defaults={"page"=1, "search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "admin/tag/{workspaceTagId}/check/workspace/page/{page}/search/{search}",
     *     name="claro_admin_workspace_tag_check_workspace_pager_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspaceTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "workspaceTagId", "strictId" = true}
     * )
     *
     * @EXT\Template()
     *
     * Renders list of workspaces that can be tagged by given tag
     *
     * @param WorkspaceTag $tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkAddableWorkspacesPagerAction(
        WorkspaceTag $workspaceTag,
        $page,
        $search
    )
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $workspacePager = $this->tagManager->getAddableWorkspacesPagerBySearch(
            $workspaceTag,
            $page,
            $search
        );

        return array(
            'workspaceTag' => $workspaceTag,
            'workspaces' => $workspacePager,
            'search' => $search
        );
    }

    /**
     * @EXT\Route(
     *     "admin/tag/{workspaceTagId}/add/workspace/{workspaceId}",
     *     name="claro_admin_workspace_tag_add_to_workspace",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspaceTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "workspaceTagId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Add Tag to Workspace
     *
     * @param WorkspaceTag $workspaceTag
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function addAdminWorkspaceTagToWorkspace(
        WorkspaceTag $workspaceTag,
        AbstractWorkspace $workspace
    )
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $relWsTag = $this->tagManager
            ->getAdminTagRelationByWorkspaceAndTag($workspace, $workspaceTag);

        if ($relWsTag === null) {
            $this->tagManager->createTagRelation($workspaceTag, $workspace);
        }

        return new Response('success', 204);
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
    public function removeAdminWorkspaceTagFromWorkspace(
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
        $relWorkspaceTag = $this->tagManager
            ->getAdminTagRelationByWorkspaceAndTag($workspace, $workspaceTag);
        $this->tagManager->deleteTagRelation($relWorkspaceTag);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "admin/tag/display/hierarchy",
     *     name="claro_display_admin_workspace_tag_hierarchy",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Render a page where admin tags can be organized
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayAdminWorkspaceTagHierarchyAction()
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
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
            'rootTags' => $rootTags
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/workspace/tag/manage/page/{page}",
     *     name="claro_manage_admin_workspace_tag",
     *     defaults={"page"=1, "search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/admin/workspace/tag/manage/page/{page}/search/{search}",
     *     name="claro_manage_admin_workspace_tag_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Manage admin workspace tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageAdminWorkspaceTagAction($page, $search)
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
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
            'rootTags' => $rootTags
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
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
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
    )
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
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
                    $msg .= ' [' . $workspace->getName()
                        . ' (' . $workspace->getCode() . ')] ';
                    $msg .= $this->translator->trans(
                        'has_been_put_in_category',
                        array(),
                        'platform'
                    );
                    $msg .= ' [' . $tag->getName() . ']';
                    $this->get('session')->getFlashBag()->add('success', $msg);
                } else {// Set success flashbag message
                    $msg = $this->translator->trans(
                        'the_workspace',
                        array(),
                        'platform'
                    );
                    $msg .= ' [' . $workspace->getName()
                        . ' (' . $workspace->getCode() . ')] ';
                    $msg .= $this->translator->trans(
                        'is_already_in_category',
                        array(),
                        'platform'
                    );
                    $msg .= ' [' . $tag->getName() . ']';
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
     * @EXT\Method("GET")
     * @EXT\Template()
     *
     * Display list of admin workspace tags
     *
     */
    public function organizeAdminWorkspaceTagAction()
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
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
            'rootTags' => $rootTags
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
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        if (is_null($workspaceTag->getUser())) {
            $this->tagManager->deleteTag($workspaceTag);
        }

        return new Response('success', 204);
    }
}
