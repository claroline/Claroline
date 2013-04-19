<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Form\WorkspaceTagType;
use Claroline\CoreBundle\Form\AdminWorkspaceTagType;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class WorkspaceTagController extends Controller
{
    const ABSTRACT_WS_CLASS = 'ClarolineCoreBundle:Workspace\AbstractWorkspace';

    /**
     * @Route(
     *     "/tag",
     *     name="claro_workspace_manage_tag"
     * )
     * @Method("GET")
     *
     * Display a table showing tags associated to user's workspaces
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageTagAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException();
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
     *
     * Display a table showing tags associated to user's workspaces
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageAdminTagAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException();
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
     *
     * Renders the Tag creation form
     *
     * @return Response
     */
    public function workspaceTagCreateFormAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException();
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
     *
     * Renders the Tag creation form
     *
     * @return Response
     */
    public function workspaceAdminTagCreateFormAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException();
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
            throw new AccessDeniedHttpException();
        }

        $user = $this->get('security.context')->getToken()->getUser();

        if ($user->getId() != $userId) {
            throw new AccessDeniedHttpException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $workspaceTag = new WorkspaceTag();
        $workspaceTag->setUser($user);

        $form = $this->createForm(new WorkspaceTagType(), $workspaceTag);
        $request = $this->getRequest();
        $form->bind($request);

        if ($form->isValid()) {
            $em->persist($workspaceTag);
            $em->flush();

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
     *
     * Creates a new Tag
     *
     * @return RedirectResponse
     */
    public function workspaceAdminTagCreateAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $workspaceTag = new WorkspaceTag();
        $workspaceTag->setUser(null);

        $form = $this->createForm(new AdminWorkspaceTagType(), $workspaceTag);
        $request = $this->getRequest();
        $form->bind($request);

        if ($form->isValid()) {
            $em->persist($workspaceTag);
            $em->flush();

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
            throw new AccessDeniedHttpException();
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (is_null($user) || is_null($workspace)) {
            throw new \RuntimeException('User, Workspace cannot be null');
        } elseif ($user->getId() != $userId) {
            throw new AccessDeniedHttpException();
        }

        $tag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findOneBy(array('name' => $tagName, 'user' => $user->getId()));

        if ($tag === null) {
            $tag = new WorkspaceTag();
            $tag->setName($tagName);
            $tag->setUser($user);
            $em->persist($tag);
            $em->flush();
        }

        $relWsTag = $em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneByWorkspaceAndTagAndUser($workspace, $tag, $user);

        if ($relWsTag == null) {
            $relWsTag = new RelWorkspaceTag();
            $relWsTag->setWorkspace($workspace);
            $relWsTag->setTag($tag);
            $em->persist($relWsTag);
            $em->flush();
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
            throw new AccessDeniedHttpException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (is_null($workspace)) {
            throw new \RuntimeException('Workspace cannot be null');
        }

        $tag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findOneBy(array('name' => $tagName, 'user' => null));

        if ($tag === null) {
            $tag = new WorkspaceTag();
            $tag->setName($tagName);
            $tag->setUser(null);
            $em->persist($tag);
            $em->flush();
        }

        $relWsTag = $em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneAdminByWorkspaceAndTag($workspace, $tag);

        if ($relWsTag === null) {
            $relWsTag = new RelWorkspaceTag();
            $relWsTag->setWorkspace($workspace);
            $relWsTag->setTag($tag);
            $em->persist($relWsTag);
            $em->flush();
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
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $workspaceTag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')->find($workspaceTagId);
        $user = $this->get('security.context')->getToken()->getUser();

        if (is_null($user) || is_null($workspace) || is_null($workspaceTag)) {
            throw new \RuntimeException('User, Workspace or Tag cannot be null');
        }

        if (!$this->get('security.context')->isGranted('ROLE_USER')
            || $user->getId() !== $workspaceTag->getUser()->getId()
            || $user->getId() != $userId) {
            throw new AccessDeniedHttpException();
        }

        $relWorkspaceTag = $em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneByWorkspaceAndTagAndUser($workspace, $workspaceTag, $user);
        $em->remove($relWorkspaceTag);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/workspace/{workspaceId}/tag/remove/{workspaceTagId}",
     *     name="claro_workspace_admin_tag_remove",
     *     options={"expose"=true}
     * )
     * @Method("DELETE")
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
            throw new AccessDeniedHttpException();
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $workspaceTag = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')->find($workspaceTagId);

        if (is_null($workspace) || is_null($workspaceTag)) {
            throw new \RuntimeException('Workspace or Tag cannot be null');
        }

        $relWorkspaceTag = $em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneAdminByWorkspaceAndTag($workspace, $workspaceTag);
        $em->remove($relWorkspaceTag);
        $em->flush();

        return new Response('success', 204);
    }
}
