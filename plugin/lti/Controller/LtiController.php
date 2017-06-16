<?php

namespace UJM\LtiBundle\Controller;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UJM\LtiBundle\Entity\LtiApp;
use UJM\LtiBundle\Form\AppType;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('administration_tool_lti')")
 */
class LtiController extends Controller
{
    /**
     * @Route("/apps", name="ujm_admin_lti")
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function appAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = new LtiApp();
        $form = $this->createForm(new AppType(), $entity);
        $vars['form'] = $form->createView();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
            }
        }
        $apps = $em->getRepository('UJMLtiBundle:LtiApp')->findAll();
        $vars['apps'] = $apps;

        return $this->render('UJMLtiBundle:Lti:app.html.twig', $vars);
    }

    /**
     * @Route("/edit/form/app/{appId}", name="ujm_lti_edit_form_app")
     *
     * @param int appId
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editFormAction($appId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $app = $em->getRepository('UJMLtiBundle:LtiApp')->find($appId);

        if (!$app) {
            throw $this->createNotFoundException('No app found');
        }

        $form = $this->createForm(new AppType(), $app);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('ujm_admin_lti');
        }

        return $this->render('UJMLtiBundle:Lti:appEdit.html.twig', [
            'form' => $form->createView(),
            'appId' => $app->getId(),
        ]);
    }

    /**
     * @Route("/delete/app/{appId}", name="ujm_lti_delete_app")
     *
     * @param int appId
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($appId)
    {
        $em = $this->getDoctrine()->getManager();
        $app = $em->getRepository('UJMLtiBundle:LtiApp')->find($appId);
        $em->remove($app);
        $em->flush();

        return $this->forward('UJMLtiBundle:Lti:app');
    }
}
