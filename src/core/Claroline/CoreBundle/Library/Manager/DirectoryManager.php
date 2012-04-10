<?php

namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Symfony\Component\Form\FormFactory;
use Claroline\CoreBundle\Form\DirectoryType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DirectoryManager
{
    /** @var Doctrine\ORM\EntityManager */
    protected $em;
    
    /** @var RightManagerInterface */
    protected $rightManager;  
    
    /** @var FormFactory */
    protected $formFactory;
    
    /** @var ContainerInterface */
    protected $container;

    public function __construct(FormFactory $formFactory, EntityManager $em, RightManagerInterface $rightManager, ContainerInterface $container)
    {
        $this->em = $em;
        $this->rightManager = $rightManager;
        $this->formFactory=$formFactory;
        $this->container=$container;
    }
     
    public function getDirectoriesOfUser($user)
    {
        $directories = $this->em->getRepository('ClarolineCoreBundle:Resource\Directory')->findBy(array('user' => $user->getId()));
        
        return $directories;
    }
    
    public function getDirectoryContentById($id)
    {
        $dir =$this->em->getRepository('ClarolineCoreBundle:Resource\Directory')->find($id);
        $resources = $this->getDirectoryContent($dir);
        
        return $resources;
    }
    
    public function getDirectoryContent($dir)
    {         
        $resources = $this->em->getRepository('ClarolineCoreBundle:Resource\Directory')->children($dir, true, 'name');
        return $resources;
    }
    
    public function getNavigableDirectoryContentById($id)
    {
         $dir =$this->em->getRepository('ClarolineCoreBundle:Resource\Directory')->find($id);
         $resources = $this->getNavigableDirectoryContent($dir);
         
         return $resources;
    }
    
    public function getNavigableDirectoryContent($dir)
    {
        $resources = $this->em->getRepository('ClarolineCoreBundle:Resource\Directory')->getNavigableChildren($dir);
        
        return $resources;
    }
    
    public function findAll()
    {
        $resources = $this->em->getRepository('ClarolineCoreBundle:Resource\Directory')->findAll();
        
        return $resources; 
    }
    
    public function getForm()
    {
        $form = $this->formFactory->create(new DirectoryType, new Directory());
        
        return $form;
    }
    
    public function addDirectory($name, $user, $dirId)
    {
        $directory = new Directory();
        $directory->setName($name);
        $directory->setUser($user);
        $dir =$this->em->getRepository('ClarolineCoreBundle:Resource\Directory')->find($dirId);
        $directory->setParent($dir);
        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneBy(array('type' => 'directory'));
        $directory->setResourceType($resourceType);
        $this->em->persist($directory);
        $this->em->flush();
        
        return $directory;
    }
    
    public function delete($directory)
    {
        $this->removeResourcesFromSubDirectories($directory);
        $this->em->remove($directory);
        $this->em->flush();
    }
    
    public function deleteById($id)
    {
       $directory = $this->em->getRepository('ClarolineCoreBundle:Resource\Directory')->find($id);
       $this->delete($directory);
    }
    
    private function removeResourcesFromDirectory($directory)
    {
        $rep = $this->em->getRepository('ClarolineCoreBundle:Resource\Directory');
        $resources = $rep->getNotDirectoryDirectChildren($directory);
        
        foreach ($resources as $resource)
        {
            $rsrcServName = $resource->getResourceType()->getService();
            $rsrcServ = $this->getContainer()->get($rsrcServName);
            $rsrcServ->delete($resource);           
        }
    }
    
    private function removeResourcesFromSubDirectories($directory)
    {
        $rep = $this->em->getRepository('ClarolineCoreBundle:Resource\Directory');
        $directories = $rep->getDirectoryDirectChildren($directory);
        $this->removeResourcesFromDirectory($directory);
        
        foreach ($directories as $directory)
        {
            $resources = $rep->getNotDirectoryDirectChildren($directory);
        
            foreach ($resources as $resource)
            {
                $rsrcServName = $resource->getResourceType()->getService();
                $rsrcServ = $this->getContainer()->get($rsrcServName);
                $rsrcServ->delete($resource);           
            }
        }
    }
     
    public function getContainer()
    {
        return $this->container;
    }
    
}