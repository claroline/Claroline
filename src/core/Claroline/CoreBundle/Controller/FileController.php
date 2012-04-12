<?php
namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Form\FileType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Library\Plugin\ClarolineControllerInterface;

class FileController extends Controller implements ClarolineControllerInterface
{
    public function indexAction()
    {
        $fileManager = $this->get('claroline.file.manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $files = $fileManager->getResourcesOfUser($user);
        $formFile = $fileManager->getForm(); 
        
        return $this->render(
            'ClarolineCoreBundle:File:index.html.twig', array(
            'files' => $files, 'formfile' => $formFile->createView())
        );
    }
    
    //ajax submission: no route
    public function formAction()
    {
        $fileManager = $this->get('claroline.file.manager');
        $formFile = $fileManager->getForm();  
        
        return $this->render(
            'ClarolineCoreBundle:Resource:generic_form.html.twig', array('form' => $formFile->createView())
        );
    }
    
    //"normal" submission: must have a route
    public function addToDirectoryAction($id)
    {
        $fileManager = $this->get('claroline.file.manager');
        $formFile = $fileManager->getForm();  
        return $this->render(
            'ClarolineCoreBundle:File:index.html.twig', array('formfile' => $formFile->createView(), 'dirId' => $id)
        );
    }
    
    public function addAction($id)
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new FileType());
        $form->bindRequest($request);
        
        if ($form->isValid())
        {
            $fileManager = $this->get('claroline.file.manager');
            $file = $form['file']->getData();
            $fileName = $file->getClientOriginalName();
            $user = $this->get('security.context')->getToken()->getUser();          
            $parent = $this->get('claroline.resource.manager')->find($id);
            $file = $fileManager->upload($file, $fileName, $user, $parent);
            
            if($request->isXmlHttpRequest()) 
            {                    
                $content = $this->renderView( 'ClarolineCoreBundle:Resource:resource.json.twig', array('root' => $file));
                $response = new Response($content);
                $response->headers->set('Content-Type', 'application/json');
            
                return $response;
            }
            
            return new Response("success");
            //return new RedirectResponse('claro_resource_index');
        }
        
        return new Response("failure");
    }
    
    public function deleteAction($id)
    {
        $fileManager = $this->get('claroline.file.manager');
        $fileManager->deleteById($id);
        
        //$route = $this->get('router')->generate('claro_resource_index');
        //return $this->redirect($route);
        return new Response("0");
    }
    
    public function viewAction($id)
    {
        $fileManager = $this->get('claroline.file.manager');
        $response = $fileManager->setDownloadResponseById($id, new Response());
        
        return $response;
    }
}