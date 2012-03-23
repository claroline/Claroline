<?php

namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Form\FileType;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Symfony\Component\Form\FormFactory;

class FileManager extends ResourceManager
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var string
     */
    protected $dir;
    protected $formFactory;
    /** @var RightManagerInterface */
    protected $rightManager;
    
    
    public function __construct(FormFactory $formFactory, EntityManager $em, RightManagerInterface $rightManager, $dir)
    {   
        $this->em = $em;
        $this->rightManager = $rightManager;
        $this->formFactory = $formFactory; 
        $this->dir = $dir;
    }
    
    public function setDownloadResponseById($id, Response $response)
    {
        $file = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\File')->find($id);
        $response = $this->setDownloadResponse($file, $response);
        
        return $response;
    }
         
    public function setDownloadResponse(File $file, Response $response)
    {
        $response = $this->setDownloadHeaders($file, $response);
        return $response;
    }
    
    public function upload($tmpFile, $fileName, $user, $parent)
    {
         $size = filesize($tmpFile);
         $hashName = $this->GUID();
         $tmpFile->move($this->dir, $hashName);
         $file = new File();
         $file->setSize($size);
         $file->setName($fileName);
         $file->setHashName($hashName);   
         $file->setUser($user);
         $file->setParent($parent);
         $resourceType = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => 'file'));
         $file->setResourceType($resourceType);
         $this->em->persist($file);
         $this->em->flush();
    }
    
    public function deleteById($id)
    {
        $file = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\File')->find($id);
        $this->delete($file);
    }
       
    public function delete(File $file)
    {
        $this->removeFile($file);
        $this->em->remove($file);
        $this->em->flush();
    }
     
    public function findAll()
    {
        $files = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\File')->findAll();
        
        return $files;
    }
    
    public function findById($id)
    {
        $file = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\File')->find($id);
        
        return $file;
    }
    
    public function getForm()
    {
        $form = $this->formFactory->create(new FileType, new File());
        
        return $form;
    }
    
    private function removeFile(File $file)
    {
        $pathName = $this->dir . DIRECTORY_SEPARATOR . $file->getHashName();
        chmod($pathName, 0777);
        unlink($pathName);
        $this->em->remove($file);
        $this->em->flush();
    }
    
    private function setDownloadHeaders(File $file, Response $response)
    {
        $response->setContent(file_get_contents($this->dir . DIRECTORY_SEPARATOR . $file->getHashName()));
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $file->getName());
        $response->headers->set('Content-Length', $file->getSize());
        $response->headers->set('Content-Type', 'application/' .  pathinfo($file->getName(), PATHINFO_EXTENSION));
        $response->headers->set('Connection', 'close');
        
        return $response;
    }  

    public function getResourcesOfUser($user)
    {
        $files = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\File')->findBy(array('user' => $user->getId()));
        
        return $files;        
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
