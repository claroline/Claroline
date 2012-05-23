<?php

namespace Claroline\HTMLPageBundle\Manager;

use Claroline\CoreBundle\Library\Manager\ResourceInterface;
use Symfony\Component\Form\FormFactory;
use Claroline\HTMLPageBundle\Form\HTMLPageType;
use Claroline\HTMLPageBundle\Entity\HTMLElement;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HTMLPageManager implements ResourceInterface
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
        $form['archive']->getData()->move($this->filesDir, $hashName);
        $htmlElement = new HTMLElement();
        $htmlElement->setHashName($hashName);
        $htmlElement->setName(pathinfo($zipName, PATHINFO_FILENAME));
        $htmlElement->setIndex($form['index_page']->getData());
        $this->em->persist($htmlElement);
        $this->em->flush();
        
        return $htmlElement;
    }
    
    public function delete($resource)
    {
        $this->emptyDir($this->pageDir.'/'.pathinfo($resource->getHashName(), PATHINFO_FILENAME));
        rmdir($this->pageDir.'/'.pathinfo($resource->getHashName(), PATHINFO_FILENAME));
        $pathName = $this->filesDir . DIRECTORY_SEPARATOR . $resource->getHashName();
        chmod($pathName, 0777);
        unlink($pathName);
        $this->em->remove($resource);  
        $this->em->flush();
    }
    
    public function indexAction($id)
    {
        return new Response("index HTML");
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
        
        return new RedirectResponse($fp);
    }    
    
    public function copy($resource, $user)
    {
        
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
            mt_rand(0, 65535), mt_rand(0, 65535)
        );
    } 
    
    private function emptyDir($dir)
    {
         $iterator = new \DirectoryIterator($dir);
         
         foreach ($iterator as $item)
         {
             if($item->isFile() && $item->getFileName()!='placeholder' && $item->getFileName()!='.gitignore')
             {
                 chmod($item->getPathname(), 0777);
                 unlink($item->getPathname());
             }
             if($item->isDir() && ($item->isDot()==null) && $item->getFilename() !="tmp" && $item->getFilename()!="thumbs")
             {
                 $this->emptyDir($item->getPathname());
                 rmdir($item->getPathname());
             }
         }
    }
}
