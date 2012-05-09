<?php

namespace Claroline\HTMLPageBundle\Manager;

use Claroline\CoreBundle\Library\Manager\ResourceInterface;
use Symfony\Component\Form\FormFactory;
use Claroline\HTMLPageBundle\Form\HTMLPageType;
use Claroline\HTMLPageBundle\Entity\HTMLElement;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;

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
        $hashName = $this->GUID().".zip";
        $form['archive']->getData()->move($this->dir, $hashName);
        //$this->unzipTmpFile($hashNameWithoutExtension);
        //$this->setExtractedContent($this->dir.DIRECTORY_SEPARATOR.$hashNameWithoutExtension, $user);
        //$iterator = new \DirectoryIterator($t);
        $HTMLElement = new HTMLElement();
        $HTMLElement->setUser($user);
        $HTMLElement->setHashName($hashName);
        $HTMLElement->setName(pathinfo($zipName, PATHINFO_FILENAME).".htm");
        $resourceType = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => 'HTMLElement'));
        $HTMLElement->setResourceType($resourceType);        
        $this->em->persist($HTMLElement);
        $this->em->flush();
        
        return $HTMLElement;
    }
    
    public function getDefaultAction($id)
    {
        $response = new Response(); 
        
        $resource = $this->em->getRepository('Claroline\HTMLPageBundle\Entity\HTMLElement')->find($id);
        $hashName = $resource->getHashName();
        $this->unzipTmpFile($hashName);
        
        return $response;
    }    
    
    private function unzipTmpFile($hashName)
    {
        $dir = $this->dir;
        $path = $dir . DIRECTORY_SEPARATOR . $hashName;
        $zip = new \ZipArchive();
        
        if ($zip->open($path) === true)
        {
            $zip->extractTo($this->dir . DIRECTORY_SEPARATOR . pathinfo($hashName, PATHINFO_FILENAME) . DIRECTORY_SEPARATOR);
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
