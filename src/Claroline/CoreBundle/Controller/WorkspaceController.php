<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\CoreBundle\Entity\Workspace;
use Claroline\CoreBundle\Form\WorkspaceType;

class WorkspaceController extends Controller
{

    /**
     *
     * @return \Claroline\CoreBundle\Entity\ACLWorkspaceManager
     */
    public function getWorkspaceManager()
    {
        return $this->get('claroline.core.acl_workspace_manager');
    }

    public function getUser()
    {
        return $this->get('security.context')->getToken()->getUser();
    }


    public function newAction()
    {
        $workspace = new Workspace();
        $form = $this->createForm(new WorkspaceType(), $workspace);

        return $this->render('ClarolineCoreBundle:Workspace:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    
    public function createAction()
    {
        $workspace_manager = $this->getWorkspaceManager();

        $workspace = new Workspace();
        $form = $this->createForm(new WorkspaceType(), $workspace);
        $request = $this->getRequest();
        $form->bindRequest($request);
        $workspace->setOwner($this->getUser());

        if ($form->isValid())
        {
            $workspace_manager->create($workspace);

            return $this->redirect($this->generateUrl('claro_core_desktop'));
        }

        return $this->render('ClarolineCoreBundle:Workspace:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    public function deleteAction($id)
    {
        $workspace_manager = $this->getWorkspaceManager();

        $workspaceRepo = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Workspace');
        $workspace = $workspaceRepo->find($id);

        $workspace_manager->delete($workspace);
        
        $this->get('session')->setFlash('notice', 'Workspace successfully deleted');            

        return $this->redirect($this->generateUrl('claro_core_desktop'));
    }
    /*
    public function nodeAction()
    {
        // create ws
        $ws = new Workspace();
        $ws->setName('Workspace 1');

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($ws);
        $em->flush();
        */

        /*
        // retrieve ws 1
        $em = $this->getDoctrine()->getEntityManager();
        $ws = $em->find('Claroline\CoreBundle\Entity\Workspace', 1);
        */

        /*
        // create test tree
        $em = $this->getDoctrine()->getEntityManager();

        $parentNode = new \Claroline\CoreBundle\Entity\Node();
        $childNode1 = new \Claroline\CoreBundle\Entity\Node();
        $childNode2 = new \Claroline\CoreBundle\Entity\Node();

        $parentNode->setName('Parent node');
        $childNode1->setName('Child node 1');
        $childNode2->setName('Child node 2');

        $childNode1->setParent($parentNode);
        $childNode2->setParent($parentNode);

        $em->persist($parentNode);
        $em->persist($childNode1);
        $em->persist($childNode2);

        $em->flush();
    }*/
}