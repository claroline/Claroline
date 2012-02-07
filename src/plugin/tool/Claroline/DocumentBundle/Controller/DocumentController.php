<?php

namespace Claroline\DocumentBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\DocumentBundle\Entity\Document;
use Claroline\DocumentBundle\Form\DocumentType;

class DocumentController extends Controller
{
    public function uploadAction()
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new DocumentType());
        $form->bindRequest($request);

        if ($form->isValid())
        {
            $fileName = $form['file']->getData()->getClientOriginalName();
            $dir = $this->container->getParameter('claroline.files.directory');
            $tmpDir = $form['file']->getData();
            $size = filesize($tmpDir);
            $form['file']->getData()->move($dir, $fileName);

            $document = new Document();
            $document->setSize($size);
            $document->setName($fileName);
            $document->setPath($dir);
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($document);
            $em->flush();
        }
        //$this->getRequest()->getSession()->setFlash("notice",$this->get('translator')->trans('upload_success', array(), 'document'));
        $this->getRequest()->getSession()->setFlash("notice", "size = " . $size);
        return $this->getFormAction();
    }

    public function getFormAction()
    {
        $document = new Document();
        $form = $this->createForm(new DocumentType(), $document);

        return $this->render('ClarolineDocumentBundle:Document:form.html.twig', array(
                'form' => $form->createView(),));
    }

    public function listAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $documents = $em->getRepository('ClarolineDocumentBundle:Document')->findAll();

        return $this->render(
            'ClarolineDocumentBundle:Document:list.html.twig', array('documents' => $documents)
        );
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $document = new Document();
        $document = $em->getRepository('ClarolineDocumentBundle:Document')->find($id);
        $pathName = $document->getPath() . DIRECTORY_SEPARATOR . $document->getName();
        chmod($pathName, 0777);
        unlink($pathName);
        $em->remove($document);
        $em->flush();

        $this->getRequest()->getSession()->setFlash("notice", $this->get('translator')->trans('delete_success', array(), 'document'));

        return $this->listAction();
    }

    public function downloadAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $document = $em->getRepository('ClarolineDocumentBundle:Document')->find($id);
        $pathName = $document->getPath() . DIRECTORY_SEPARATOR . $document->getName();
        $size = $document->getSize();
        $ext = pathinfo($pathName, PATHINFO_EXTENSION);
        
        $response = new Response();

        $response->setContent(file_get_contents($pathName));
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $document->getName());
        $response->headers->set('Content-Length', $size);
        $response->headers->set('Content-Type', 'application/' . $ext);
        $response->headers->set('Connection', 'close');
        
        $this->getRequest()->getSession()->setFlash("notice", "taille = ".$size);
        
        return $response;
    }



}