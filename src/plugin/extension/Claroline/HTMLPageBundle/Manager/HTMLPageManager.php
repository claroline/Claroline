<?php

namespace Claroline\HTMLPageBundle\Manager;

use Claroline\CoreBundle\Library\Manager\ResourceInterface;
use Symfony\Component\Form\FormFactory;
use Claroline\HTMLPageBundle\Form\HTMLPageType;
use Claroline\HTMLPageBundle\Entity\HTMLElement;
use Doctrine\ORM\EntityManager;

class HTMLPageManager// implements ResourceInterface
{
    /** @var string */
    private $dir;
    
    /** @var FormFactory */
    private $formFactory;
    
    /** @var EntityManager */
    private $em;
    
    public function __construct($dir, FormFactory $formFactory, EntityManager $em)
    {
        $this->dir = $dir;
        $this->formFactory = $formFactory;
        $this->em = $em;
    }
            
    public function getForm()
    {
        $form = $this->formFactory->create(new HTMLPageType());
        
        return $form;
    }
    
    public function getResourceType()
    {
        return "HTMLElement";
    }
    
    public function add($form, $id, $user)
    {
        $tmpZip = $form['archive']->getData();
        $zipName = $tmpZip->getClientOriginalName();
        $hashNameWithoutExtension = $this->GUID();
        $form['archive']->getData()->move($this->dir, "{$hashNameWithoutExtension}.zip");
        $this->unzipTmpFile($hashNameWithoutExtension);
        $this->setExtractedContent($this->dir.DIRECTORY_SEPARATOR.$hashNameWithoutExtension, $user);
        //$iterator = new \DirectoryIterator($t);
        $HTMLElement = new HTMLElement();
        $HTMLElement->setUser($user);
        $HTMLElement->setHashName($hashNameWithoutExtension.".zip");
        $HTMLElement->setName($zipName);
        $resourceType = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => 'HTMLElement'));
        $HTMLElement->setResourceType($resourceType);        
        $this->em->persist($HTMLElement);
        $this->em->flush();
    }
    
    private function unzipTmpFile($hashNameWithoutExtension)
    {
        $dir = $this->dir;
        $path = $dir . DIRECTORY_SEPARATOR . $hashNameWithoutExtension.".zip";
        $zip = new \ZipArchive();
        
        if ($zip->open($path) === true)
        {
            $zip->extractTo($this->dir . DIRECTORY_SEPARATOR . $hashNameWithoutExtension . DIRECTORY_SEPARATOR);
            $zip->close();
        }
        else
        {
            return new \Exception("zip extraction error");
        }

        //$this->uploadDirectory($dir . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $hashDir, $root);
        //$this->emptyDir($dir . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $hashDir);
        //rmdir($dir . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $hashDir);
        //chmod($dir . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $zipName, 0777);
        //unlink($dir . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $zipName);
    }
    
    private function addHTMLElement($dir, \DirectoryIterator $item, $user)
    {
        $HTMLElement = new HTMLElement();
        $HTMLElement->setUser($user);
        $hashName = $this->GUID();
        $HTMLElement->setHashName($hashName);
        $pathName = $item->getPathname();
        $HTMLElement->setName($pathName);
        $resourceType = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => 'HTMLElement'));
        $HTMLElement->setResourceType($resourceType);   
        rename($pathName, $this->dir.DIRECTORY_SEPARATOR.$hashName);
        $this->em->persist($HTMLElement);
    }
    
    private function setExtractedContent($dir, $user)
    {
        $iterator = new \DirectoryIterator($dir);
        foreach($iterator as $item)
        {
            if($item->isFile())
            {
                $this->addHTMLElement($dir, $item, $user);  
            }
            if ($item->isDir() == true && $item->isDot() != true)
            {
                $this->setExtractedContent($item->getPathname(),$user);
            }
        }
        
        $this->em->flush();
        rmdir($dir);        
    }
    /*
    private function uploadDirectory($dir, Directory $root)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $iterator = new \DirectoryIterator($dir);
        foreach ($iterator as $item)
        {
            if ($item->isFile())
            {
                $this->uploadDocumentItem($item, $root);
            }
            if ($item->isDir() == true && $item->isDot() != true)
            {
                $directory = new Directory();
                $directory->setName($item->getBasename());
                $directory->setParent($root);
                $em->persist($root);
                $em->persist($directory);
                $this->uploadDirectory($dir . DIRECTORY_SEPARATOR . $directory->getName(), $directory);
            }
        }
    }

    private function uploadDocumentItem(\DirectoryIterator $file, $root)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $document = new Document();
        $hashName = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $document->setName($file->getFileName());
        $document->setSize($file->getSize());
        $document->setHashName($hashName);
        $root->addDocument($document);
        $em->persist($root);
        $em->persist($document);
        copy($file->getPathName(), $this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR . $hashName);
    }
    
    private function emptyDir($dir)
    {
         $iterator = new \DirectoryIterator($dir);
         
         foreach ($iterator as $item)
         {
             if($item->isFile())
             {
                 chmod($item->getPathname(), 0777);
                 unlink($item->getPathname());
             }
             if($item->isDir() && ($item->isDot()==null))
             {
                 $this->emptyDir($item->getPathname());
                 rmdir($item->getPathname());
             }
         }
    }*/
    
    private function GUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535),
            mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535),
            mt_rand(0, 65535), mt_rand(0, 65535));
    }
    
}
