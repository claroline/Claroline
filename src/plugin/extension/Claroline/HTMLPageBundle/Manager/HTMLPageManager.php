<?php

namespace Claroline\HTMLPageBundle\Manager;

use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Claroline\CoreBundle\Library\Services\ThumbnailGenerator;
use Symfony\Component\Form\FormFactory;
use Claroline\CoreBundle\Form\FileType;
use Claroline\CoreBundle\Library\Manager\FileManager;
use Claroline\HTMLPageBundle\Entity\HTMLElement;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HTMLPageManager extends FileManager
{
    /**@ var string */
    private $pageDir;

    private $router;
    
    public function __construct(FormFactory $formFactory, EntityManager $em, RightManagerInterface $rightManager, $dir, $templating, ThumbnailGenerator $thumbnailGenerator, $router, $pageDir)
    {
        parent::__construct($formFactory, $em, $rightManager, $dir, $templating, $thumbnailGenerator);
        
        $this->router = $router;
        $this->pageDir = $pageDir;
    }
            
    public function getResourceType()
    {
        return "HTMLElement";
    }
 
    public function indexAction($id)
    {
        return new Response("index PAGEMANAGER HTML");
    }
    
    public function getDefaultAction($id)
    {
        $ds = DIRECTORY_SEPARATOR;
        
        $resource = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\File')->find($id);
        $hashName = $resource->getHashName();
        $this->unzipTmpFile($hashName);
        $relativePath = pathinfo($resource->getHashName(), PATHINFO_FILENAME).$ds.pathinfo($resource->getName(), PATHINFO_FILENAME).$ds."index.html";
        $route = $this->router->getContext()->getBaseUrl();
        $fp = preg_replace('"/web/app_dev.php$"', "/web/HTMLPage/$relativePath", $route);
        
        return new RedirectResponse($fp);
    } 
    
    private function unzipTmpFile($hashName)
    {
        $path = $this->dir . DIRECTORY_SEPARATOR . $hashName;
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
}
