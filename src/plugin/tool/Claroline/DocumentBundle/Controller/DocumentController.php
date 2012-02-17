<?php
//à changer delete directory
//vérifier les fichiers temporaires de php
//directory setChildren ne fonctionne pas bug?
//upload 2x le même zip => bug !
//change orginaleFileName

namespace Claroline\DocumentBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\DocumentBundle\Entity\Document;
use Claroline\DocumentBundle\Entity\Directory;
use Claroline\DocumentBundle\Form\DocumentType;
use Claroline\DocumentBundle\Form\DirectoryType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentController extends Controller
{
    public function uploadDocumentAction($id)
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new DocumentType());
        $form->bindRequest($request);

        if ($form->isValid())
        {
            $fileName = $form['file']->getData()->getClientOriginalName();
            $dir = $this->container->getParameter('claroline.files.directory');
            if(pathinfo($fileName, PATHINFO_EXTENSION) == 'zip')
            {
                $zipName = $this->genTmpZipName().'.zip';  
                $newDirName = explode('.',$fileName);
                $form['file']->getData()->move($dir.DIRECTORY_SEPARATOR.'tmp',$zipName);
                $this->unzipTmpFile($zipName,$id,$newDirName[0]);
                chmod($dir.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$zipName, 0777);
                unlink($dir.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$zipName);           
            }
            else
            {
                $tmpDir = $form['file']->getData();
                $size = filesize($tmpDir);
                $hashName = hash('md5', $fileName . time());
                $form['file']->getData()->move($dir, $hashName);
                $document = new Document();
                $document->setSize($size);
                $document->setName($fileName);
                $document->setHashName($hashName);
                $em = $this->getDoctrine()->getEntityManager();
                $currentDirectory = $em->getRepository('ClarolineDocumentBundle:Directory')->find($id);
                $currentDirectory->addDocument($document);
                $em->persist($currentDirectory);
                $em->persist($document);
                $em->flush();
            }
        }

        $msg = $this->get('translator')->trans('upload_success', array(), 'document');
        $this->getRequest()->getSession()->setFlash('notice', $msg);
        $url = $this->generateUrl('claro_directory_show', array('id' => $id));

        return $this->redirect($url);
    }

    public function deleteDocumentAction($dir, $id)
    {
        $this->removeDocument($id);
        $this->getRequest()->getSession()->setFlash("notice", $this->get('translator')->trans('delete_success', array(), 'document'));
        $url = $this->generateUrl('claro_directory_show', array('id' => $dir));

        return $this->redirect($url);
    }

    public function moveDocument()
    {
        
    }

    public function downloadDocumentAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $document = $em->getRepository('ClarolineDocumentBundle:Document')->find($id);
        $pathName = $this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR . $document->getHashName();
        $size = $document->getSize();
        $ext = pathinfo($document->getName(), PATHINFO_EXTENSION);
        $response = new Response();
        $response->setContent(file_get_contents($pathName));
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $document->getName());
        $response->headers->set('Content-Length', $size);
        $response->headers->set('Content-Type', 'application/' . $ext);
        $response->headers->set('Connection', 'close');
        $this->getRequest()->getSession()->setFlash("notice", "taille = " . $size);
        
        return $response;
    }
    
    public function addDirectoryAction($id)
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new DirectoryType());
        $form->bindRequest($request);

        if ($form->isValid())
        {
            $directoryName = $form['name']->getData();
            $directory = new Directory();
            $directory->setName($directoryName);
            $em = $this->getDoctrine()->getEntityManager();
            $currentDirectory = $em->getRepository('ClarolineDocumentBundle:Directory')->find($id);
            $directory->setParent($currentDirectory);
            $em->persist($directory);
            $em->persist($currentDirectory);
            $em->flush();
        }

        $this->getRequest()->getSession()->setFlash("notice", "new directory done");
        return $this->showDirectoryAction($id);
    }

    public function showDirectoryAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $currentDirectory = $this->checkDirectory($id);
        $document = new Document();
        $formDoc = $this->createForm(new DocumentType($id), $document);
        $directory = new Directory();
        $formDir = $this->createForm(new DirectoryType(), $directory);
        $documents = $currentDirectory->getDocuments();
        $rep = $em->getRepository('ClarolineDocumentBundle:Directory');
        $directories = $rep->children($currentDirectory, true);

        foreach ($directories as $directory)
        {
            $directory->setPathName($this->getDirectoryRealPath($directory));
        }

        $listParentDirectories = $this->getListParentDirectories($currentDirectory);

        return $this->render(
            'ClarolineDocumentBundle:Document:showDirectory.html.twig', array(
                'documents' => $documents, 'directories' => $directories, 'currentDirectory' => $currentDirectory, 'listParent' => $listParentDirectories, 'formdoc' => $formDoc->createView(), 'formdir' => $formDir->createView()));
    }

    public function downloadDirectoryAction($id)
    {
        $zipFile = new \ZipArchive();
        $zipName =   $hashName = hash("md5", "tmpzip" . time());
        $pathZip = $this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR ."tmp". DIRECTORY_SEPARATOR .$zipName;
        $zipFile->open($pathZip,\ZIPARCHIVE::CREATE);
        $em = $this->getDoctrine()->getEntityManager();
        $rep = $em->getRepository('ClarolineDocumentBundle:Directory');
        $currentDir = $rep->find($id);             
        $directories = $rep->children($currentDir);
        
        foreach($directories as $directory)
        {
            $pathDir = $this->getRelativeDirectoryPath($currentDir, $directory, $directory->getName());
            $zipFile->addEmptyDir($pathDir); 
            $documents = $directory->getDocuments();
            
            foreach ($documents as $document)
            {
                $zipFile->addFile($this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR.$document->getHashName(), $pathDir.DIRECTORY_SEPARATOR.$document->getName());
            }
        }
        
        $documents = $currentDir->getDocuments();
        
        foreach($documents as $document)
        {
             $zipFile->addFile($this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR.$document->getHashName(), $currentDir->getName().DIRECTORY_SEPARATOR.$document->getName());
        }
        
        $zipFile->close();     
        $response = new Response();
        $response->setContent(file_get_contents($pathZip));
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=zip-a-dee-doo-dah.zip');
        $response->headers->set('Content-Type', 'application/' . '.zip');
        $response->headers->set('Connection', 'close');       
        chmod($pathZip, 0777);
        unlink($pathZip);
        
        return $response;
    }

    public function deleteDirectoryAction($id)
    {

        $em = $this->getDoctrine()->getEntityManager();
        $rep = $em->getRepository('ClarolineDocumentBundle:Directory');
        $rmdir = $rep->find($id);
        $currentDirectory = $rmdir->getParent($id);
        $this->removeDocumentsFromSubDirectories($rmdir);
        $em->remove($rmdir);
        $em->flush();
        $this->getRequest()->getSession()->setFlash("notice", "directory removed");

        return $this->showDirectoryAction($currentDirectory->getId());
    }

    public function getDirectoryRealPath(Directory $directory)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $tabPath = $em->getRepository('ClarolineDocumentBundle:Directory')->getPath($directory);
        $path = "";

        foreach ($tabPath as $var)
        {
            $path = $path . DIRECTORY_SEPARATOR . $var->getName() . "\n";
        }

        return $path;
    }

    public function getListParentDirectories(Directory $directory)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $tabPath = $em->getRepository('ClarolineDocumentBundle:Directory')->getPath($directory);
        $i = 0;

        foreach ($tabPath as $var)
        {
            $tabDir[$i] = new Directory();
            $tabDir[$i] = $var;
            $i++;
        }

        return $tabDir;
    }

    public function checkDirectory($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $currentDirectory = $em->getRepository('ClarolineDocumentBundle:Directory')->find($id);

        if ($currentDirectory == null)
        {
            throw new NotFoundHttpException("This directory doesn't exist");
        }

        return $currentDirectory;
    }

    public function removeDocumentsFromSubDirectories(Directory $rmdir)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $rep = $em->getRepository('ClarolineDocumentBundle:Directory');
        $directories = $rep->children($rmdir);
        $this->removeDocumentsFromDirectory($rmdir);

        foreach ($directories as $directory)
        {
            $documents = $directory->getDocuments();
            foreach ($documents as $document)
            {
                $pathName = $this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR . $document->getHashName();
                chmod($pathName, 0777);
                unlink($pathName);
            }
        }
    }

    public function removeDocumentsFromDirectory(Directory $rmdir)
    {
        $documents = $rmdir->getDocuments();

        foreach ($documents as $document)
        {
            $pathName = $this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR . $document->getHashName();
            chmod($pathName, 0777);
            unlink($pathName);
        }
    }

    public function removeDocument($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $document = $em->getRepository('ClarolineDocumentBundle:Document')->find($id);
        $pathName = $this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR . $document->getHashName();
        chmod($pathName, 0777);
        unlink($pathName);
        $em->remove($document);
        $em->flush();
    }    
    
    //1st iteration : pathName = directory name.
    public function getRelativeDirectoryPath(Directory $root, Directory $dir, $pathName)
    { 
        $parent = $dir->getParent();
                
        if ($parent->getName() != $root->getName() && $parent!=null)
        {
            $pathName=$parent->getName().DIRECTORY_SEPARATOR.$pathName;
            $pathName = $this->
                getRelativeDirectoryPath($root, $parent, $pathName);
        }else
        {
            $pathName = $root->getName().DIRECTORY_SEPARATOR.$pathName;
        }
        
        return $pathName;
    }
    
    public function unzipTmpFile($zipName, $id, $originalDirName)
    {
           $dir = $this->container->getParameter('claroline.files.directory');
           $path = $dir.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$zipName;
           $zip = new \ZipArchive();
           $hashDir = hash('md5', $zipName.time());
           if ($zip->open($path) === TRUE)
            {
                $zip->extractTo($dir.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$hashDir);
                $zip->close();
            }
            else
            {
                return new \Exception ("couldnt upload directory");
            }
            
            //this will add the root directory
            $root = new Directory();
            //must change root name
            $root->setName($originalDirName);
            
            $em = $this->getDoctrine()->getEntityManager();
            $currentDirectory = $em->getRepository('ClarolineDocumentBundle:Directory')->find($id);
            $root->setParent($currentDirectory);
            $em->persist($root);
            $em->persist($currentDirectory);
            $this->uploadDirectory($dir.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$hashDir, $root);
            $this->emptyDir($dir.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$hashDir, $root);
            $em->flush();        
    }
    
    public function uploadDirectory($dir,Directory $root)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $iterator = new \DirectoryIterator($dir);
        foreach ($iterator as $item)
        {
            if ($item->isFile())
            {
                $this->uploadDocumentItem($item, $root);
            }
            if ($item->isDir()==true && $item->isDot()!=true)
            {              
                $directory = new Directory();
                $directory->setName($item->getBasename());
                $directory->setParent($root);
                $em->persist($root);
                $em->persist($directory);
                $this->uploadDirectory($dir.DIRECTORY_SEPARATOR.$directory->getName(),$directory);
            }
        }
    }
    
    public function uploadDocumentItem(\DirectoryIterator $file, $root)
    {
        $dir = $this->container->getParameter('claroline.files.directory');
        $em = $this->getDoctrine()->getEntityManager();   
        $document = new Document();
        $hashName =  base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $document->setName($file->getFileName());
        $document->setSize($file->getSize());
        $document->setHashName($hashName);
        $root->addDocument($document);
        $em->persist($root);
        $em->persist($document);
        copy($file->getPathName(),$this->container->getParameter('claroline.files.directory').DIRECTORY_SEPARATOR.$hashName);
    }
    
    function emptyDir($dir)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir),
                \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $path)
        {
            if ($path->isDir())
            {
                rmdir($path->__toString());
            }
            else
            {
                chmod($path->__toString(), 0777);
                unlink($path->__toString());
            }
        }
        rmdir($dir);
    }
    
    function genTmpZipName($length = 8)
    {     
    $chars = 'bcdfghjklmnprstvwxzaeiouAZERTYUIOPQSDFGHJKLMWXCVBN2134567890';
    $result ='';
   
    for ($p = 0; $p < $length; $p++)
    {
        $result .=  $chars[mt_rand(0, 57)];
    }
   
    return $result;
    }
}