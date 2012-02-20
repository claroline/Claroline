<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Form\WorkspaceType;
use Claroline\CoreBundle\Library\Workspace\Configuration;

class WorkspaceController extends Controller
{
    public function newAction()
    {
        // check if granted
        
        $workspace = new SimpleWorkspace();
        $form = $this->get('form.factory')->create(new WorkspaceType(), $workspace);

        return $this->render(
            'ClarolineCoreBundle:Workspace:form.html.twig', 
            array('form' => $form->createView())
        );
    }
    
    public function createAction()
    {
        // check if granted
              
        $workspace = new SimpleWorkspace();
        $form = $this->get('form.factory')->create(new WorkspaceType(), $workspace);
        $form->bindRequest($this->request);
        $user = $this->get('security.context')->getToken()->getUser();

        if ($form->isValid())
        {
            $config = new Configuration();
            $config->setName($workspace->getName());
            $wsCreator = $this->get('claroline.workspace.creator');
            $wsCreator->createWorkspace($config, $user);
            
            $route = $this->get('router')->generate('claroline_desktop_index');
            
            return new RedirectResponse($route);
        }

        return $this->render(
            'ClarolineCoreBundle:Workspace:form.html.twig',
            array('form' => $form->createView())
        );
    }
    
    public function deleteAction($id)
    {
        $workspace = $em->find('ClarolineCoreBundle:Workspace', $id);
        
        if (false === $this->get('security.context')->isGranted('DELETE', $workspace))
        {
            throw new AccessDeniedHttpException();
        }
        
        $em->remove($workspace);
        $em->flush();
        
        $this->get('session')->setFlash('notice', 'Workspace successfully deleted');            
        $route = $this->get('router')->generate('claroline_desktop_index');
       
        return new RedirectResponse($route);
    }
}