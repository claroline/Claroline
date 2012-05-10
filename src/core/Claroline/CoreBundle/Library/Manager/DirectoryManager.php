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
    public function getForm()
    {
        $form = $this->formFactory->create(new DirectoryType, new Directory());
        
        return $form;
    }
    
    public function add($form, $id, $user)
    {
        $directory = new Directory();
        $name = $form['name']->getData();
        $directory->setName($name);
        $directory->setUser($user);
        $dir =$this->em->getRepository('ClarolineCoreBundle:Resource\Directory')->find($id);
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
    
    public function getResourceType()
    {
        return "directory";
    }
    
    public function getDefaultAction($id)
    {
        $formResource = $this->formFactory->create(new SelectResourceType(), new ResourceType());
        $resources = $this->resourceManager->getChildrenById($id);
        $directory = $this->em->getRepository('ClarolineCoreBundle:Resource\Directory')->find($id);
        $resourcesType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:index.html.twig', array('form_resource' => $formResource->createView(), 'resources' => $resources, 'id' => $id, 'resourcesType' => $resourcesType, 'directory' => $directory));
        $response = new Response($content);
        
        return $response;
    }    
    
    public function indexAction($id)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:Directory:index.html.twig', array('id' => $id));
        $response = new Response($content);
        
        return $response;
    }
    
    public function copy($resource)
    {
        
    }
    // FIN DES METHODES OBLIGATOIRES
     
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
    
    private function removeResourcesFromDirectory($directory)
    {
        $rep = $this->em->getRepository('ClarolineCoreBundle:Resource\Directory');
        $resources = $rep->getNotDirectoryDirectChildren($directory);
        
        foreach ($resources as $resource)
        {
            $rsrcServName = $this->findRsrcServ($resource->getResourceType());
            $rsrcServ = $this->container->get($rsrcServName);
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
                $rsrcServName = $this->findRsrcServ($resource->getResourceType());
                $rsrcServ = $this->container->get($rsrcServName);
                $rsrcServ->delete($resource);           
            }
        }
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