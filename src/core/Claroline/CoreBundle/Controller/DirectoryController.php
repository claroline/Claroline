<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\ChooseResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Form\DirectoryType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Library\Plugin\ClarolineControllerInterface;

class DirectoryController extends Controller implements ClarolineControllerInterface
{
    public function viewAction($id)
    {
        $formResource = $this->get('form.factory')->create(new ChooseResourceType(), new ResourceType());
        $currentDirectory = $this->get('claroline.resource.manager')->find($id);
        
        $resources = $this->get('claroline.resource.manager')->getChildrenById($id);
        
        return $this->render(
            'ClarolineCoreBundle:Directory:view.html.twig', array('form_resource' => $formResource->createView(), 'resources' => $resources, 'id' => $id, 'currentDirectory' => $currentDirectory )
        );
    }
   
    public function formAction()
    {
        $directoryManager =  $this->get('claroline.directory.manager');
        $formDir = $directoryManager ->getForm();
        
        return $this->render(
            'ClarolineCoreBundle:Resource:generic_form.html.twig', array('form' => $formDir->createView())
        ); 
    }
    
    public function addToDirectoryAction($id)
    {
        $directoryManager =  $this->get('claroline.directory.manager');
        $formDir = $directoryManager ->getDirectoryForm();
        
        return $this->render(
            'ClarolineCoreBundle:Directory:index.html.twig', array('form_directory' => $formDir->createView(), 'id' => $id)
        );
    }
    
    public function addAction($id)
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new DirectoryType(), new Directory());
        $form->bindRequest($request);
        
        if ($form->isValid())
        {
             $directoryManager = $this->get('claroline.directory.manager');
             $directoryName = $form['name']->getData();
             $user = $this->get('security.context')->getToken()->getUser();
             $directory = $directoryManager->addDirectory($directoryName, $user, $id);
             $em = $this->getDoctrine()->getEntityManager();
             $resources = $em->getRepository('Claroline\CoreBundle\Entity\Resource\Directory')->children($directory, true);
             
             if($request->isXmlHttpRequest()) 
             {   
                 /*
                $content = $this->renderView( 'ClarolineCoreBundle:Resource:resource.json.twig', array('resources' => $resources, 'root' => $directory));
                $response = new Response($content);
                $response->headers->set('Content-Type', 'application/json');*/
                
                return new Response("success");
             }
             
             $url = $this->generateUrl('claro_resource_index'); 
             return $this->redirect($url);     
        }
        else
        {
             return $this->render(
                'ClarolineCoreBundle:Resource:generic_form.html.twig', array('form' => $form->createView())
             ); 
        }
    }    
    
    public function deleteAction($id)
    {
        $directoryManager = $this->get('claroline.directory.manager');  
        $directoryManager->deleteById($id);
        
        //$url = $this->generateUrl('claro_resource_index');
        //return $this->redirect($url);  
        return new Response("0");
    }
}