<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Form\ToolType;

class AdministrationToolController extends Controller
{

    /**
     * @Route(
     *     "/tool/show",
     *     name="claro_admin_tool_show"
     * )
     *
     * @Template("ClarolineCoreBundle:Administration:desktopToolNames.html.twig")
     *
     * change the desktop tool name.
     *
     * @return Response
     */
    public function showToolAction()
    {
        $tool = new Tool();
        $forms = array();
        $em = $this->getDoctrine()->getManager();
        $tools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->findAll();

        foreach ($tools as $i => $tool) {
            $forms[] = $this->createForm(new ToolType(), $tool);
            $forms[$i] = $forms[$i]->createView();
        }

        return array(
            'forms' => $forms,
            'tools' => $tools
        );
    }

     /**
     * @Route(
     *     "/tool/modify/{id}",
     *     name="claro_admin_tool_modify"
     * )
     * @Method("POST")
     *
     * change the desktop tool name.
      *
     * @param integer $id
      *
     * @return Response
     */
    public function modifyToolAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($id);
        $form = $this->createForm(new ToolType(), $tool);
        $request = $this->get('request');
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $em->persist($tool);
                $em->flush();
            }
        }

        return($this->showToolAction());
    }
}