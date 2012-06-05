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
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Form\SelectResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

class DirectoryManager implements ResourceInterface
{
    /** @var Doctrine\ORM\EntityManager */
    protected $em;
    
    /** @var RightManagerInterface */
    protected $rightManager;  
    
    /** @var FormFactory */
    protected $formFactory;
    
    /** @var ContainerInterface */
    protected $container;
    
    /** @var ResourseManager */
    protected $resourceManager;
    
    protected $templating;

    public function __construct(FormFactory $formFactory, EntityManager $em, RightManagerInterface $rightManager, ContainerInterface $container, ResourceManager $resourceManager, $templating)
    {
        $this->em = $em;
        $this->rightManager = $rightManager;
        $this->formFactory=$formFactory;
        $this->container=$container;
        $this->resourceManager = $resourceManager;
        $this->templating = $templating;
    }
    
    //METHODES OBLIGATOIRE A PARTIR D'ICI
    public function getFormPage($twigFile, $id, $type)
    {
        $form = $this->formFactory->create(new DirectoryType(), new Directory());
        $content = $this->templating->render(
            $twigFile, array('form' => $form->createView(), 'id' => $id, 'type' =>$type)
        );
        
        return $content;
    }
    
    public function add($form, $id, $user)
    {
        $directory = new Directory();
        $name = $form['name']->getData();
        $directory->setName($name);
        $this->em->persist($directory);
        $this->em->flush();
        
        return $directory;
    }
    
    public function copy($resource, $user)
    {
        $directory = new Directory();
        $directory->setName($resource->getName());
        $this->em->persist($directory);
        $this->em->flush();
        
        return $directory;
    }
    
    //different than other resourcesmanager: it must works with resource instances
    public function delete($resourceInstance)
    {
        /*  recursive suppression
       
        $children = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($resourceInstance, false);
        
        foreach($children as $child)
        {
            if($child->getResourceType()->getType()!='directory')
            {
                $rsrc = $child->getResource();
                $this->em->remove($child);
                $rsrc->removeInstance(); 
                
                if($rsrc->getInstanceAmount() == 0)
                {
                    $type = $child->getResourceType();
                    $srv = $this->findRsrcServ($type);
                    $this->container->get($srv)->delete($child->getResource());
                }
            }
            else
            {
                $rsrc = $child->getResource();
                $this->em->remove($child);
                $this->em->remove($rsrc);
            }
        }
        
        $rsrc = $resourceInstance->getResource();
        $this->em->remove($rsrc);
        */
        
        //non recursive suppression: not sure if it works
        $children = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($resourceInstance, true);
        {
            foreach($children as $child)
            {
                if($child->getResourceType()->getType()!='directory')
                {
                    $rsrc = $child->getResource();
                    $this->em->remove($child);
                    $rsrc->removeInstance(); 
                
                    if($rsrc->getInstanceAmount() == 0)
                    {
                        $type = $child->getResourceType();
                        $srv = $this->findRsrcServ($type);
                        $this->container->get($srv)->delete($child->getResource());
                    }
                }            
                else
                {
                    $rsrc = $child->getResource();
                    $this->em->remove($child);
                    $rsrc->removeInstance(); 
                    
                    if($rsrc->getInstanceAmount() == 0)
                    {
                        $type = $child->getResourceType();
                        $this->em->remove($rsrc);
                    }
                }
            }
        }
        
        $rsrc = $resourceInstance->getResource();
        $this->em->remove($resourceInstance);
        $rsrc->removeInstance(); 

        if($rsrc->getInstanceAmount() == 0)
        {
            $type = $resourceInstance->getResourceType();
            $this->em->remove($rsrc);
        }
        $this->em->flush();
    }
    
    public function getResourceType()
    {
        return "directory";
    }
    
    public function getDefaultAction($id)
    {
        $formResource = $this->formFactory->create(new SelectResourceType(), new ResourceType());
        $resourceInstance = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($id);
        $workspace = $resourceInstance->getWorkspace();
        $resourcesInstance = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($resourceInstance, true);
        $resourcesType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:index.html.twig', array('form_resource' => $formResource->createView(), 'resources' => $resourcesInstance, 'id' => $id, 'resourcesType' => $resourcesType, 'directory' => $resourceInstance, 'workspace' => $workspace));
        $response = new Response($content);
        
        return $response;
    }    
    
    public function indexAction($workspaceId, $id)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:Directory:index.html.twig', array('id' => $id));
        $response = new Response($content);
        
        return $response;
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
    
    public function deleteById($id)
    {
       $directory = $this->em->getRepository('ClarolineCoreBundle:Resource\Directory')->find($id);
       $this->delete($directory);
    }
       
    private function findRsrcServ($resourceType)
    {
        $services = $this->container->getParameter("resource.service.list");
        $names = array_keys($services);
        $serviceName = null;
        
        foreach($names as $name)
        {
            $type = $this->container->get($name)->getResourceType();
            
            if($type == $resourceType->getType())
            {
                $serviceName = $name;
            }
        }
        
        return $serviceName;
    }
}