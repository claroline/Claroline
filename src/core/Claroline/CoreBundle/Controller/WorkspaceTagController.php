<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Form\WorkspaceTagType;
use Claroline\CoreBundle\Form\AdminWorkspaceTagType;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;

class WorkspaceTagController extends Controller
{
    const ABSTRACT_WS_CLASS = 'ClarolineCoreBundle:Workspace\AbstractWorkspace';

    /**
     * @Route(
     *     "/tag",
     *     name="claro_workspace_manage_tag"
     * )
     * @Method("GET")
     * @Secure(roles="ROLE_USER")
     *
     * Display a table showing tags associated to user's workspaces
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageTagAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $tags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findBy(array('user' => $user->getId()));
        $workspaces = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->findByUser($user);
        $workspacesTags = array();

        foreach ($workspaces as $workspace) {
            $relWsTagsByWs = $em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
                ->findAllByWorkspaceAndUser($workspace, $user);
            $workspacesTags[$workspace->getId()] = $relWsTagsByWs;
        }

        $tagsNameTxt = '[';
        foreach ($tags as $tag) {
            $tagsNameTxt .= '"' . $tag->getName() . '",';
        }
        $tagsNameTxt = substr($tagsNameTxt, 0, strlen($tagsNameTxt) - 1);
        $tagsNameTxt .= ']';

        return $this->render(
            'ClarolineCoreBundle:Workspace:manage_tag.html.twig',
            array(
                'user' => $user,
                'tagsNameTxt' => $tagsNameTxt,
                'workspaces' => $workspaces,
                'workspacesTags' => $workspacesTags
            )
        );
    }

    /**
     * @Route(
     *     "/tag/admin",
     *     name="claro_workspace_manage_admin_tag"
     * )
     * @Method("GET")
     * @Secure(roles="ADMIN")
     *
     * Display a table showing tags associated to user's workspaces
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageAdminTagAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $tags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findByUser(null);
        $workspaces = $em->getRepository(self::ABSTRACT_WS_CLASS)->findNonPersonal();
        $workspacesTags = array();

        foreach ($workspaces as $workspace) {
            $relWsTagsByWs = $em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
                ->findAdminByWorkspace($workspace);
            $workspacesTags[$workspace->getId()] = $relWsTagsByWs;
        }

        $tagsNameTxt = '[';
        foreach ($tags as $tag) {
            $tagsNameTxt .= '"' . $tag->getName() . '",';
        }
        $tagsNameTxt = substr($tagsNameTxt, 0, strlen($tagsNameTxt) - 1);
        $tagsNameTxt .= ']';

        return $this->render(
            'ClarolineCoreBundle:Workspace:manage_admin_tag.html.twig',
            array(
                'user' => $user,
                'tagsNameTxt' => $tagsNameTxt,
                'workspaces' => $workspaces,
                'workspacesTags' => $workspacesTags
            )
        );
    }

    /**
     * @Route(
     *     "/tag/createform",
     *     name="claro_workspace_tag_create_form"
     * )
     * @Method("GET")
     * @Secure(roles="ROLE_USER")
     *
     * Renders the Tag creation form
     *
     * @return Response
     */
    public function workspaceTagCreateFormAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $user = $this->get('security.context')->getToken()->getUser();
        $workspaceTag = new WorkspaceTag();
        $workspaceTag->setUser($user);
        $form = $this->createForm(new WorkspaceTagType(), $workspaceTag);

        return $this->render(
            'ClarolineCoreBundle:Workspace:workspace_tag_form.html.twig',
            array('form' => $form->createView(), 'user' => $user)
        );
    }

    /**
     * @Route(
     *     "/tag/admin/createform",
     *     name="claro_workspace_admin_tag_create_form"
     * )
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     *
     * Renders the Tag creation form
     *
     * @return Response
     */
    public function workspaceAdminTagCreateFormAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $workspaceTag = new WorkspaceTag();
        $workspaceTag->setUser(null);
        $form = $this->createForm(new AdminWorkspaceTagType(), $workspaceTag);

        return $this->render(
            'ClarolineCoreBundle:Workspace:workspace_admin_tag_form.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/tag/create/{userId}",
     *     name="claro_workspace_tag_create"
     * )
     * @Method("POST")
     * @Secure(roles="ROLE_USER")
     *
     * Creates a new Tag
     *
     * @param integer $userId
     *
     * @return RedirectResponse
     */
    public function workspaceTagCreateAction($userId)
    {
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $user = $this->get('security.context')->getToken()->getUser();

        if ($user->getId() != $userId) {
            throw new AccessDeniedException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $tagManager = $this->get('claroline.manager.workspace_tag_manager');
        $workspaceTag = new WorkspaceTag();
        $workspaceTag->setUser($user);

        $form = $this->createForm(new WorkspaceTagType(), $workspaceTag);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $tagManager->insert($workspaceTag);
            $tagManager->createTagHierarchy($workspaceTag, $workspaceTag, 0);

            return $this->redirect(
                $this->generateUrl('claro_workspace_manage_tag')
            );
        }

        return $this->render(
            'ClarolineCoreBundle:Workspace:workspace_tag_form.html.twig',
            array('form' => $form->createView(), 'user' => $user)
        );
    }

    /**
     * @Route(
     *     "/tag/admin/create",
     *     name="claro_workspace_admin_tag_create"
     * )
     * @Method("POST")
     * @Secure(roles="ROLE_ADMIN")
     *
     * Creates a new Tag
     *
     * @return RedirectResponse
     */
    public function workspaceAdminTagCreateAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $tagManager = $this->get('claroline.manager.workspace_tag_manager');
        $workspaceTag = new WorkspaceTag();
        $workspaceTag->setUser(null);

        $form = $this->createForm(new AdminWorkspaceTagType(), $workspaceTag);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $tagManager->insert($workspaceTag);
            $tagManager->createTagHierarchy($workspaceTag, $workspaceTag, 0);

            return $this->redirect(
                $this->generateUrl('claro_workspace_manage_admin_tag')
            );
        }

        return $this->render(
            'ClarolineCoreBundle:Workspace:workspace_admin_tag_form.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/{userId}/workspace/{workspaceId}/tag/add/{tagName}",
     *     name="claro_workspace_tag_add",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     * @Secure(roles="ROLE_USER")
     *
     * Add Tag to Workspace
     *
     * @param integer $userId
     * @param integer $workspaceId
     * @param string $tagName
     *
     * @return Response
     */
    public function addTagToWorkspace($userId, $workspaceId, $tagName)
    {
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        $tagManager = $this->get('claroline.manager.workspace_tag_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (is_null($user) || is_null($workspace)) {
            throw new \RuntimeException('User, Workspace cannot be null');
        } elseif ($user->getId() != $userId) {
            throw new AccessDeniedException();
        }

        $tag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findOneBy(array('name' => $tagName, 'user' => $user->getId()));

        if ($tag === null) {
            $tag = $tagManager->createTag($tagName, $user);
            $tagManager->createTagHierarchy($tag, $tag, 0);
        }

        $relWsTag = $em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneByWorkspaceAndTagAndUser($workspace, $tag, $user);

        if ($relWsTag == null) {
            $tagManager->createTagRelation($tag, $workspace);
        }

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/workspace/{workspaceId}/tag/add/{tagName}",
     *     name="claro_workspace_admin_tag_add",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     * @Secure(roles="ROLE_ADMIN")
     *
     * Add Tag to Workspace
     *
     * @param integer $workspaceId
     * @param string $tagName
     *
     * @return Response
     */
    public function addAdminTagToWorkspace($workspaceId, $tagName)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $tagManager = $this->get('claroline.manager.workspace_tag_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (is_null($workspace)) {
            throw new \RuntimeException('Workspace cannot be null');
        }

        $tag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findOneBy(array('name' => $tagName, 'user' => null));

        if ($tag === null) {
            $tag = $tagManager->createTag($tagName);
            $tagManager->createTagHierarchy($tag, $tag, 0);
        }

        $relWsTag = $em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneAdminByWorkspaceAndTag($workspace, $tag);

        if ($relWsTag === null) {
            $tagManager->createTagRelation($tag, $workspace);
        }

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/{userId}/workspace/{workspaceId}/tag/remove/{workspaceTagId}",
     *     name="claro_workspace_tag_remove",
     *     options={"expose"=true}
     * )
     * @Method("DELETE")
     * @Secure(roles="ROLE_USER")
     *
     * Remove Tag from Workspace
     *
     * @param integer $userId
     * @param integer $workspaceId
     * @param integer $workspaceTagId
     *
     * @return Response
     */
    public function removeTagFromWorkspace($userId, $workspaceId, $workspaceTagId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $tagManager = $this->get('claroline.manager.workspace_tag_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $workspaceTag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')->find($workspaceTagId);
        $user = $this->get('security.context')->getToken()->getUser();

        if (is_null($user) || is_null($workspace) || is_null($workspaceTag)) {
            throw new \RuntimeException('User, Workspace or Tag cannot be null');
        }

        if (!$this->get('security.context')->isGranted('ROLE_USER')
            || $user->getId() !== $workspaceTag->getUser()->getId()
            || $user->getId() !== (int) $userId) {
            throw new AccessDeniedException();
        }

        $relWorkspaceTag = $em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneByWorkspaceAndTagAndUser($workspace, $workspaceTag, $user);
        $tagManager->deleteTagRelation($relWorkspaceTag);

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/workspace/{workspaceId}/tag/remove/{workspaceTagId}",
     *     name="claro_workspace_admin_tag_remove",
     *     options={"expose"=true}
     * )
     * @Method("DELETE")
     * @Secure(roles="ROLE_ADMIN")
     *
     * Remove admin Tag from Workspace
     *
     * @param integer $workspaceId
     * @param integer $workspaceTagId
     *
     * @return Response
     */
    public function removeAdminTagFromWorkspace($workspaceId, $workspaceTagId)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $tagManager = $this->get('claroline.manager.workspace_tag_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $workspaceTag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')->find($workspaceTagId);

        if (is_null($workspace) || is_null($workspaceTag)) {
            throw new \RuntimeException('Workspace or Tag cannot be null');
        }

        $relWorkspaceTag = $em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneAdminByWorkspaceAndTag($workspace, $workspaceTag);
        $tagManager->deleteTagRelation($relWorkspaceTag);

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/tag/admin/organize",
     *     name="claro_workspace_organize_admin_tag"
     * )
     * @Method("GET")
     *
     * Render a page where admin tags can be organized
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function organizeWorkspaceAdminTag()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $tags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findBy(array('user' => null), array('name' => 'ASC'));
        $tagsHierarchy = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
            ->findAllAdmin();
        $rootTags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findAdminRootTags();
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

        return $this->render(
            'ClarolineCoreBundle:Workspace:organize_admin_tag.html.twig',
            array(
                'tags' => $tags,
                'hierarchy' => $hierarchy,
                'rootTags' => $rootTags
            )
        );
    }

    /**
     * @Route(
     *     "/tag/organize",
     *     name="claro_workspace_organize_tag"
     * )
     * @Method("GET")
     *
     * Render a page where tags can be organized
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function organizeWorkspaceTag()
    {
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $tags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findBy(array('user' => $user->getId()), array('name' => 'ASC'));

        $tagsHierarchy = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
            ->findAllByUser($user);
        $rootTags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findRootTags($user);
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

        return $this->render(
            'ClarolineCoreBundle:Workspace:organize_tag.html.twig',
            array(
                'tags' => $tags,
                'hierarchy' => $hierarchy,
                'rootTags' => $rootTags
            )
        );
    }

    /**
     * @Route(
     *     "/tag/admin/check/children/{tagId}",
     *     name="claro_workspace_admin_tag_check_children"
     * )
     * @Method("GET")
     *
     * Render a page where children can be added to a tag
     *
     * @param integer $tagId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkPotentialWorkspaceAdminTagChildren($tagId)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $tag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findOneById($tagId);
        $children = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findAdminChildren($tag);
        $possibleChildren = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findPossibleAdminChildren($tag);

        return $this->render(
            'ClarolineCoreBundle:Workspace:check_admin_tag_children.html.twig',
            array(
                'tag' => $tag,
                'children' => $children,
                'possibleChildren' => $possibleChildren
            )
        );
    }

    /**
     * @Route(
     *     "/tag/check/children/{tagId}",
     *     name="claro_workspace_tag_check_children"
     * )
     * @Method("GET")
     *
     * Render a page where children can be added to a tag
     *
     * @param integer $tagId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkPotentialWorkspaceTagChildren($tagId)
    {
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $tag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findOneBy(array('id' => $tagId, 'user' => $user));
        $children = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findChildren($user, $tag);
        $possibleChildren = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findPossibleChildren($user, $tag);

        return $this->render(
            'ClarolineCoreBundle:Workspace:check_tag_children.html.twig',
            array(
                'tag' => $tag,
                'children' => $children,
                'possibleChildren' => $possibleChildren
            )
        );
    }

    /**
     * @Route(
     *     "/tag/admin/add/children/{tagId}/{childrenString}",
     *     name="claro_workspace_admin_tag_add_children",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Create hierarchy link between given admin tag and a given list of admin tags
     *
     * @param integer $tagId
     * @param string $childrenString
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAdminTagChildren($tagId, $childrenString)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $children = explode(',', $childrenString);

        if (is_array($children) && count($children) > 0) {

            $em = $this->get('doctrine.orm.entity_manager');
            $tagManager = $this->get('claroline.manager.workspace_tag_manager');
            $tag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
                ->findOneById($tagId);
            // Get all hierarchies where param $tag is a child
            $tagsHierarchy = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
                ->findBy(array('user' => null , 'tag' => $tag));
            // Get all hierarchies where parent is in param
            $childrenhierarchies = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
                ->findAdminHierarchiesByParents($children);

            foreach ($childrenhierarchies as $childHierarchy) {

                foreach ($tagsHierarchy as $tagHierarchy) {
                    $tagManager->createTagHierarchy(
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
     * @Route(
     *     "/tag/add/children/{tagId}/{childrenString}",
     *     name="claro_workspace_tag_add_children",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Create hierarchy link between given tag and a given list of tags
     *
     * @param integer $tagId
     * @param string $childrenString
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addTagChildren($tagId, $childrenString)
    {
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();

        $children = explode(',', $childrenString);

        if (is_array($children) && count($children) > 0) {
            $tagManager = $this->get('claroline.manager.workspace_tag_manager');

            $tag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
                ->findOneBy(array('user' => $user, 'id' => $tagId));
            // Get all hierarchies where param $tag is a child
            $tagsHierarchy = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
                ->findBy(array('user' => $user , 'tag' => $tag));
            // Get all hierarchies where parent is in param
            $childrenhierarchies = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
                ->findHierarchiesByParents($user, $children);

            foreach ($childrenhierarchies as $childHierarchy) {

                foreach ($tagsHierarchy as $tagHierarchy) {
                    $tagManager->createTagHierarchy(
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
     * @Route(
     *     "/tag/admin/remove/children/{tagId}/{childrenString}",
     *     name="claro_workspace_admin_tag_remove_children",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Create hierarchy link between given admin tag and a given list of admin tags
     *
     * @param integer $tagId
     * @param string $childrenString
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAdminTagChildren($tagId, $childrenString)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $children = explode(',', $childrenString);

        if (is_array($children) && count($children) > 0) {

            $em = $this->get('doctrine.orm.entity_manager');
            $tagManager = $this->get('claroline.manager.workspace_tag_manager');
            $tag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
                ->findOneById($tagId);

            // Get all hierarchies where parent is in param $childrenString
            $childrenHierarchy = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
                ->findAdminHierarchiesByParents($children);

            // Get an array with all parents id
            $parentsTagsId = array();
            $parentsTags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
                ->findAdminParentsFromTag($tag);

            foreach ($parentsTags as $parentTag) {
                $parentsTagsId[] = $parentTag->getId();
            }

            // Get an array with all children id
            $childrenTagsId = array();
            $childrenTags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
                ->findAdminChildrenFromTags($children);

            foreach ($childrenTags as $childTag) {
                $childrenTagsId[] = $childTag->getId();
            }

            // Get all hierarchies where parents are in array $parentsTagsId and children in $childrenTagsId
            $multiHierarchies = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
                ->findAdminHierarchiesByParentsAndChildren($parentsTagsId, $childrenTagsId);

            // Get all hierarchies where given tag (parent) is a child
            $parentHierarchies = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
                ->findBy(array('tag' => $tag));

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
                        $tagManager->deleteTagHierarchy($singleHierarchy);
                    }
                }
            }
        }

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/tag/remove/children/{tagId}/{childrenString}",
     *     name="claro_workspace_tag_remove_children",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Create hierarchy link between given tag and a given list of tags
     *
     * @param integer $tagId
     * @param string $childrenString
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeTagChildren($tagId, $childrenString)
    {
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();

        $children = explode(',', $childrenString);

        if (is_array($children) && count($children) > 0) {
            $tagManager = $this->get('claroline.manager.workspace_tag_manager');

            $tag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
                ->findOneBy(array('user' => $user, 'id' => $tagId));

            // Get all hierarchies where parent is in param $childrenString
            $childrenHierarchy = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
                ->findHierarchiesByParents($user, $children);

            // Get an array with all parents id
            $parentsTagsId = array();
            $parentsTags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
                ->findParentsFromTag($user, $tag);

            foreach ($parentsTags as $parentTag) {
                $parentsTagsId[] = $parentTag->getId();
            }

            // Get an array with all children id
            $childrenTagsId = array();
            $childrenTags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
                ->findChildrenFromTags($user, $children);

            foreach ($childrenTags as $childTag) {
                $childrenTagsId[] = $childTag->getId();
            }

            // Get all hierarchies where parents are in array $parentsTagsId and children in $childrenTagsId
            $multiHierarchies = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
                ->findHierarchiesByParentsAndChildren($user, $parentsTagsId, $childrenTagsId);

            // Get all hierarchies where given tag (parent) is a child
            $parentHierarchies = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
                ->findBy(array('user' => $user, 'tag' => $tag));

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
                        $tagManager->deleteTagHierarchy($singleHierarchy);
                    }
                }
            }
        }

        return new Response('success', 204);
    }
}