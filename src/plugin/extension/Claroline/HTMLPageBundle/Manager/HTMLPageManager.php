<?php

namespace Claroline\HTMLPageBundle\Manager;

use Claroline\CoreBundle\Library\Manager\ResourceInterface;
use Symfony\Component\Form\FormFactory;
use Claroline\HTMLPageBundle\Form\HTMLPageType;
use Claroline\HTMLPageBundle\Entity\HTMLElement;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HTMLPageManager// implements ResourceInterface
{
    /** @var string */
    private $filesDir;
    
    /**@ var string */
    private $pageDir;
    
    /** @var FormFactory */
    private $formFactory;
    
    /** @var EntityManager */
    private $em;
    
    private $router;
    
    public function __construct($filesDir, FormFactory $formFactory, EntityManager $em, $router, $pageDir)
    {
        $this->filesDir = $filesDir;
        $this->formFactory = $formFactory;
        $this->em = $em;
        $this->router = $router;
        $this->pageDir = $pageDir;
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
        $HTMLElement->setName(pathinfo($zipName, PATHINFO_FILENAME));
        $HTMLElement->setIndex($form['index_page']->getData());
        $resourceType = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => 'HTMLElement'));
        $HTMLElement->setResourceType($resourceType);        
        $this->em->persist($HTMLElement);
        $this->em->flush();
        
        return $HTMLElement;
    }
    
    public function getDefaultAction($id)
    {
        $ds = DIRECTORY_SEPARATOR;
        
        $resource = $this->em->getRepository('Claroline\HTMLPageBundle\Entity\HTMLElement')->find($id);
        $hashName = $resource->getHashName();
        $this->unzipTmpFile($hashName);
        $relativePath = pathinfo($resource->getHashName(), PATHINFO_FILENAME).$ds.$resource->getName().$ds.$resource->getIndex();
        $route = $this->router->getContext()->getBaseUrl();
        $fp = preg_replace('"/web/app_dev.php$"', "/web/HTMLPage/$relativePath", $route);
        
        return $response = new RedirectResponse($fp);
    }    
    
    private function unzipTmpFile($hashName)
    {
        $path = $this->filesDir . DIRECTORY_SEPARATOR . $hashName;
        $zip = new \ZipArchive();
        
        if ($zip->open($path) === true)
        {
            $zip->extractTo($this->pageDir . DIRECTORY_SEPARATOR . pathinfo($hashName, PATHINFO_FILENAME) . DIRECTORY_SEPARATOR);
            $zip->close();
        }
        else
        {
            return 0;
            return new \Exception("zip extraction error");
        }
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
    
    //THIS MUST BE CHANGED LATER as it looks like the worst way to do this
    private function getFileDirectoryUrl()
    {
        
    }
    
}
