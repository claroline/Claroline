<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Form\WorkspaceEditType;
use Claroline\CoreBundle\Form\WorkspaceTemplateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class WorkspaceParametersController extends Controller
{
    /**
     * @Route(
     *     "/{workspaceId}/form/export",
     *     name="claro_workspace_export_form"
     * )
     * @Method("GET")
     *
     * @param integer $workspaceId
     *
     * @return Response
     *
     * @throws AccessDeniedHttpException
     */
    public function workspaceExportFormAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->get('form.factory')->create(new WorkspaceTemplateType());

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:template.html.twig',
            array(
                'form' => $form->createView(),
                'workspace' => $workspace)
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/export",
     *     name="claro_workspace_export"
     * )
     * @Method("POST")
     *
     * @param integer $workspaceId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws AccessDeniedHttpException
     */
    public function workspaceExportAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $request = $this->getRequest();
        $form = $this->createForm(new WorkspaceTemplateType());
        $form->bind($request);

        if ($form->isValid()) {
            $name = $form->get('name')->getData();
            $this->get('claroline.workspace.exporter')->export($workspace, $name);
            $route = $this->get('router')->generate(
                'claro_workspace_open_tool',
                array('toolName' => 'parameters', 'workspaceId' => $workspace->getId())
            );

            return new RedirectResponse($route);
        }

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:template.html.twig',
            array(
                'form' => $form->createView(),
                'workspace' => $workspace)
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/editform",
     *     name="claro_workspace_edit_form"
     * )
     * @Method("GET")
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function workspaceEditFormAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(new WorkspaceEditType(), $workspace);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:workspace_edit.html.twig',
            array('form' => $form->createView(), 'workspace' => $workspace)
        );
    }


    /**
     * @Route(
     *     "/{workspaceId}/edit",
     *     name="claro_workspace_edit"
     * )
     * @Method("POST")
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function workspaceEditAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $wsRegisteredName = $workspace->getName();
        $wsRegisteredCode = $workspace->getCode();
        $form = $this->createForm(new WorkspaceEditType(), $workspace);
        $request = $this->getRequest();
        $form->bind($request);

        if ($form->isValid()) {
            $em->persist($workspace);
            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_open_tool',
                    array(
                        'workspaceId' => $workspaceId,
                        'toolName' => 'parameters'
                    )
                )
            );
        } else {
            $workspace->setName($wsRegisteredName);
            $workspace->setCode($wsRegisteredCode);
        }

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:workspace_edit.html.twig',
            array('form' => $form->createView(), 'workspace' => $workspace)
        );
    }
}
