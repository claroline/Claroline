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
    
    public function addToDirectoryAction($id)
    {
        $fileManager = $this->get('claroline.file.manager');
        $formFile = $fileManager->getForm();  
        return $this->render(
            'ClarolineCoreBundle:File:index.html.twig', array('formfile' => $formFile->createView(), 'dirId' => $id)
        );
    }
    
    public function uploadAction($id)
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
            $fileManager->upload($file, $fileName, $user, $parent);
            $msg = $this->get('translator')->trans('upload_success', array(), 'document');
            $this->getRequest()->getSession()->setFlash('notice', $msg);
        }
        
        //redirect; this could be removed
        if(null != $this->get('claroline.common.history_browser')->getLastContext())
        {        
            return $this->redirect($this->get('claroline.common.history_browser')->getLastContext()->getUri());
        }
        else
        {
            $route = $this->get('router')->generate('claro_resource_index');
            return new RedirectResponse($route);
        }
    }
    
    public function deleteAction($id)
    {
        $fileManager = $this->get('claroline.file.manager');
        $fileManager->deleteById($id);
        
        $route = $this->get('router')->generate('claro_resource_index');
        return $this->redirect($route);
    }
    
    public function viewAction($id)
    {
        $fileManager = $this->get('claroline.file.manager');
        $response = $fileManager->setDownloadResponseById($id, new Response());
        return $response;
    }
}