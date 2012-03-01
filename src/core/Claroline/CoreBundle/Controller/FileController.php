<?php
namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\File;
use Claroline\CoreBundle\Form\FileType;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    public function indexAction()
    {
        $fileManager = $this->get('claroline.files.file_manager');
        $files = $fileManager->findAll();
        $file = new File();
        $formFile = $this->createForm(new FileType(), $file);
        
        return $this->render(
            'ClarolineCoreBundle:File:index.html.twig', array(
            'files' => $files, 'formfile' => $formFile->createView()));
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
            $fileManager->upload($file, $fileName);
            $msg = $this->get('translator')->trans('upload_success', array(), 'document');
            $this->getRequest()->getSession()->setFlash('notice', $msg);
        }
        
        $url = $this->generateUrl('claro_file_index');
        return $this->redirect($url);
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