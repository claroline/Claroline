<?php
namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\File;
use Claroline\CoreBundle\Form\FileType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    public function indexAction()
    {
        $fileManager = $this->get('claroline.files.file_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $files = $fileManager->getResourcesOfUser($user);
        $formFile = $fileManager->getForm();
        
        return $this->render(
            'ClarolineCoreBundle:File:index.html.twig', array(
            'files' => $files, 'formfile' => $formFile->createView())
        );
    }
    
    public function uploadAction()
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new FileType());
        $form->bindRequest($request);

        if ($form->isValid())
        {
            $fileManager = $this->get('claroline.files.file_manager');
            $file = $form['file']->getData();
            $fileName = $file->getClientOriginalName();
            $user = $this->get('security.context')->getToken()->getUser();
            $fileManager->upload($file, $fileName, $user);
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
            $route = $this->get('router')->generate('claro_file_index');
            return new RedirectResponse($route);
        }
    }
    
    public function deleteAction($id)
    {
        $fileManager = $this->get('claroline.files.file_manager');
        $fileManager->deleteById($id);
        
        $url = $this->generateUrl('claro_file_index');
        return $this->redirect($url);
    }
    
    public function downloadAction($id)
    {
        $fileManager = $this->get('claroline.files.file_manager');
        $response = $fileManager->setDownloadResponseById($id, new Response());
        return $response;
    }
}