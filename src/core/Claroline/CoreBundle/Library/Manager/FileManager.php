<?php
namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Form\FileType;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Claroline\CoreBundle\Library\Services\ThumbnailGenerator;

class FileManager implements ResourceManagerInterface
{
    /** @var EntityManager */
    protected $em;
    /** @var string */
    protected $dir;
    /** @var FormFactory */
    protected $formFactory;
    /** @var RightManagerInterface */
    protected $rightManager;
    /** @var TwigEngine */
    protected $templating;
    /** @var ThumbnilGenerator **/
    protected $thumbnailGenerator;
    
    public function __construct(FormFactory $formFactory, EntityManager $em, RightManagerInterface $rightManager, $dir, TwigEngine $templating, ThumbnailGenerator $thumbnailGenerator)
    {   
        $this->em = $em;
        $this->rightManager = $rightManager;
        $this->formFactory = $formFactory; 
        $this->dir = $dir;
        $this->templating = $templating;
        $this->thumbnailGenerator = $thumbnailGenerator;
    }
       
    public function delete($file)
    {
        $this->removeFile($file);
        $this->em->remove($file);
        $this->em->flush();
    }
     
    public function getForm()
    {
        return $this->formFactory->create(new FileType, new File());
    }
    
    public function getFormPage($twigFile, $id, $type)
    {
        $form = $this->formFactory->create(new FileType, new File());
        
        return $this->templating->render(
            $twigFile, array('form' => $form->createView(), 'id' => $id, 'type' =>$type)
        );
    }
    
    public function add($form, $id, $user)
    {
         $tmpFile = $form['name']->getData();
         $fileName = $tmpFile->getClientOriginalName();
         $extension = pathinfo($fileName, PATHINFO_EXTENSION);
         $size = filesize($tmpFile); 
         $hashName = $this->generateGuid().".".$extension;
         $tmpFile->move($this->dir, $hashName);
         $file = new File();
         $file->setSize($size);
         $file->setName($fileName);
         $file->setHashName($hashName);
         $mime = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\MimeType')->findOneBy(array('extension' => $extension));
         
         if(null === $mime)
         {
             $mime = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\MimeType')->findOneBy(array('extension' => 'default'));
         }
         
         $file->setMimeType($mime);
         $this->em->persist($file);
         $this->em->flush();
         //$this->thumbnailGenerator->createThumbNail("{$this->dir}/$hashName", "{$this->dir}/tn_{$hashName}", ThumbnailGenerator::WIDTH, ThumbnailGenerator::HEIGHT);
         
         return $file;
    }
    
    public function copy($resource, $user)
    {
        $newFile = new File();
        $newFile->setSize($resource->getSize());
        $newFile->setName($resource->getName());
        $hashName = $this->generateGuid().".".pathinfo($resource->getHashName(), PATHINFO_EXTENSION);
        $newFile->setHashName($hashName);
        $filePath = $this->dir . DIRECTORY_SEPARATOR . $resource->getHashName();
        $newPath = $this->dir . DIRECTORY_SEPARATOR . $hashName;
        copy($filePath, $newPath);
        $this->em->persist($newFile);
        $this->em->flush();
        
        return $newFile;
    }
    
    public function getResourceType()
    {
        return "file";
    }
    
    public function getDefaultAction($id)
    {
        $response = new Response();     
        $file = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\File')->find($id);
           
        return $this->setDownloadHeaders($file, $response);; 
    }
    
    public function indexAction($workspaceId, $id)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:File:index.html.twig');
        
        return new Response($content);
    }
    
    public function getPlayerName()
    {
        return "default file player";
    }
    
    /*
     * found on http://php.net/manual/fr/function.com-create-guid.php
     */
    private function generateGuid()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535),
            mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535),
            mt_rand(0, 65535), mt_rand(0, 65535));
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
    
    private function removeFile(File $file)
    {
        $pathName = $this->dir . DIRECTORY_SEPARATOR . $file->getHashName();
        chmod($pathName, 0777);
        unlink($pathName);
        $this->em->remove($file);
        $this->em->flush();
    }
}
