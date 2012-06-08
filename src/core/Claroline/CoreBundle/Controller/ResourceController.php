<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\DirectoryType;
use Claroline\CoreBundle\Form\SelectResourceType;

class ResourceController extends Controller
{
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser(); 
        $em = $this->getDoctrine()->getEntityManager();
        $formResource = $this->get('form.factory')->create(new SelectResourceType(), new ResourceType());
        $personnalWs = $user->getPersonnalWorkspace();
        $resources = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->getWSListableRootResource($personnalWs); 
        $resourcesType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findAll();

        return $this->render(
            'ClarolineCoreBundle:Resource:index.html.twig', array('form_resource' => $formResource->createView(), 'resources' => $resources, 'id' => null, 'resourcesType' => $resourcesType, 'workspace' => $personnalWs)
        );
    }
    
    public function showResourceFormAction($id)
    {
        $request = $this->get('request');
        $form = $request->request->get('select_resource_form');
        $idType = $form['type'];
        $em = $this->getDoctrine()->getEntityManager();
        $resourceType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->find($idType);
        $rsrcServName = $this->findResService($resourceType);
        $rsrcServ = $this->get($rsrcServName);
        $twigFile = 'ClarolineCoreBundle:Resource:form_page.html.twig';
        $content = $rsrcServ->getFormPage($twigFile, $id, $resourceType->getType());

        return new Response($content); 
    }
    
        
    public function getFormAction($id, $type)
    {
        $resourceType = $this->getDoctrine()->getEntityManager()->getRepository("Claroline\CoreBundle\Entity\Resource\ResourceType")->findOneBy(array('type' => $type));
        $name = $this->findResService($resourceType);
        $rsrcServ = $this->get($name);
        $twigFile = 'ClarolineCoreBundle:Resource:generic_form.html.twig';
        $content = $rsrcServ->getFormPage($twigFile, $id, $type);
        
        return new Response($content);
    }
    
    //TODO: check return type; js must know if some json is returned
    public function addAction($type, $id, $workspaceId)
    {
        $request = $this->get('request');
        $user = $this->get('security.context')->getToken()->getUser();
        
        if(null == $workspaceId)
        {
            $workspaceId = $user->getPersonnalWorkspace()->getId();
        }
        
        $resourceType = $this->getDoctrine()->getEntityManager()->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => $type));
        $name = $this->findResService($resourceType);
        $form = $this->get($name)->getForm();
        $form->bindRequest($request);
        $em = $this->getDoctrine()->getEntityManager();
 
        if($form->isValid())
        {   
            $resource = $this->get($name)->add($form, $id, $user);
            if(null!=$resource)   
            {
                $ri = new ResourceInstance();
                $ri->setUser($user);
                $dir = $em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($id);
                $ri->setParent($dir);
                $resourceType = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneBy(array('type' => $type));
                $resource->setResourceType($resourceType);        
                $rightManager = $this->get('claroline.security.right_manager');
                $ri->setCopy(false);  
                $workspace = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($workspaceId);
                $ri->setWorkspace($workspace);
                $ri->setResource($resource);
                $resource->incrInstance();
                //set sharable to sthg
                $resource->setSharable(false);
                $em->persist($ri);
                $em->flush();
                $rightManager->addRight($ri, $user, MaskBuilder::MASK_OWNER);  
                
                if($request->isXmlHttpRequest())
                {
                    $content = '{"key":'.$ri->getId().', "name":"'.$ri->getResource()->getName().'", "type":"'.$ri->getResourceType()->getType().'"}';
                    $response = new Response($content);
                    $response->headers->set('Content-Type', 'application/json');  

                    return $response;
                } 
            }
            
            $route = $this->get('router')->generate("claro_resource_index");
       
            return new RedirectResponse($route);
        }
        else
        {
            if($request->isXmlHttpRequest())
            {
                return $this->render(
                    'ClarolineCoreBundle:Resource:generic_form.html.twig', array('form' => $form->createView(), 'id' => $id, 'type' =>$type)
                );
            }
            else
            {
                return $this->render(
                    'ClarolineCoreBundle:Resource:form_page.html.twig', array ('form' => $form->createView(), 'type' => $type, 'id' => $id)
                );
            }
        }
    }
    
    //TODO: remove type from defaultClickAction
    public function defaultClickAction($type, $id)
    {
          $resourceInstance = $this->getDoctrine()->getEntityManager()->getRepository(
              'Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($id);
          
          $securityContext = $this->get('security.context');
          
          if(false == $securityContext->isGranted('VIEW', $resourceInstance))
          {
               throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
          }
          else
          {
              if($type!="null")
              {
                  $resourceType = $this->getDoctrine()->getEntityManager()->getRepository(
                      'Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => $type));
              }
              else
              {
                  $resourceType = $this->getDoctrine()->getEntityManager()->getRepository(
                      'Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($id)->getResourceType();
              }
              $name = $this->findResService($resourceType);
              
              if($type!='directory')
              {    
                  $response = $this->get($name)->getDefaultAction($resourceInstance->getResource()->getId());
              }
              else
              {
                  $response = $this->get($name)->getDefaultAction($id);
              }
          }

        return $response;
    }

    public function openAction($id, $workspaceId)
    {      
       $em = $this->getDoctrine()->getEntityManager(); 
       $resourceInstance = $em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($id);
       $securityContext = $this->get('security.context');
       
       if(false == $securityContext->isGranted('VIEW', $resourceInstance))
       {
           throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
       }
       else
       {
            $resourceType = $resourceInstance->getResourceType();
            
            if($resourceType->getType()=='file')
            {
                $name = null;
                $mime = $resourceInstance->getResource()->getMimeType();
                $name = $this->findPlayerService($mime);

                if($name == null)
                {
                    $name = $this->findResService($resourceType);
                }
            }
            else
            {
                $name = $this->findResService($resourceType);
            }
            
           $response = $this->get($name)->indexAction($workspaceId, $resourceInstance);
           
           return new Response($response);
       }
    }
    
    public function editAction($resourceId, $workspaceId, $options)
    {
        if($options == 'copy')
        {
            $em = $this->getDoctrine()->getEntityManager();
            $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($resourceId);
            $workspace = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($workspaceId);
            $user = $this->get('security.context')->getToken()->getUser();
            $name = $this->findResService($resourceInstance->getResourceType());
            $copy = $this->get($name)->copy($resourceInstance->getResource(), $user);
            
            $instanceCopy = new ResourceInstance();
            $instanceCopy->setParent($resourceInstance->getParent());
            $instanceCopy->setResource($copy);
            $instanceCopy->setCopy(false);
            $instanceCopy->setWorkspace($resourceInstance->getWorkspace());
            $copy->setResourceType($resourceInstance->getResourceType());
            $instanceCopy->setUser($user);
            
            $copy->incrInstance();
            $resourceInstance->getResource()->decrInstance();
            $em->persist($copy);
            $em->persist($instanceCopy);
            $em->remove($resourceInstance);
            $em->flush();
            
            $roleCollaborator = $workspace->getCollaboratorRole();
            $rightManager = $this->get('claroline.security.right_manager');
            $rightManager->addRight($instanceCopy, $roleCollaborator, MaskBuilder::MASK_VIEW);
            
            return new Response("copied");
        }
        else
        {
            $em = $this->getDoctrine()->getEntityManager();
            $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($resourceId);
            $name = $this->findResService($resourceInstance->getResourceType());
            $response = $this->get($name)->editAction($resourceInstance->getResource()->getId());
            
            return new Response($response);
        }
        
        
    }
    
    public function getJsonResourceNodeAction($id, $workspaceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($workspaceId);
        $response = new Response();
              
        if($id == 0)
        {
            $resourcesInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->getWSListableRootResource($workspace);
            $root = new ResourceInstance();
            $rootDir = new Directory();
            $rootDir->setName('root');
            $root->setResource($rootDir);
            $root->setId(0);
            $directoryType = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findBy(array('type' => 'directory'));
            $root->setResourceType($directoryType[0]);
            
            foreach($resourcesInstance as $resourceInstance)
            {
                $root->addChildren($resourceInstance);
            }
            
            $content = $this->renderView('ClarolineCoreBundle:Resource:dynatree_resource.json.twig', array('resources' => (array) $root));
            $response = new Response($content);
            $response->headers->set('Content-Type', 'application/json');      
        }
        else
        {
            $parent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($id);
            $resourcesInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->getListableChildren($parent);
            $content = $this->renderView('ClarolineCoreBundle:Resource:dynatree_resource.json.twig', array('resources' => $resourcesInstance));
            $response = new Response($content);
            $response->headers->set('Content-Type', 'application/json');     
        }
        
        return $response;
    }
       
    public function moveResourceAction ($idChild, $idParent)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $parent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($idParent);
        $child = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($idChild);
        $child->setParent($parent);
        $this->getDoctrine()->getEntityManager()->flush();
        
        return new Response("success");
    }
    
    public function addToWorkspaceAction($resourceId, $workspaceId, $option)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId); 
        
        if($option == 'ref' )
        {
            if($resourceId == 0)
            {
                $userWorkspace = $this->get('security.context')->getToken()->getUser()->getPersonnalWorkspace();
                $resourcesInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->getWSListableRootResource($userWorkspace);

                foreach($resourcesInstance as $resourceInstance)
                {
                    $this->copyFirstReferenceInstance($workspace, $resourceInstance->getId());
                }           
            }
            else
            {
                $this->copyFirstReferenceInstance($workspace, $resourceId);
            }  
            
            $em->flush();
        }
        else
        {
           if($resourceId == 0)
           {
               $userWorkspace = $this->get('security.context')->getToken()->getUser()->getPersonnalWorkspace();
               $resourcesInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->getWSListableRootResource($userWorkspace);

               foreach($resourcesInstance as $resourceInstance)
               {
                   $this->copyFirstCopyInstance($workspace, $resourceInstance->getId());
               }    
           }
           else
           {
               $this->copyFirstCopyInstance($workspace, $resourceId);
           }
           return new Response("copied"); 
        }
        
        return new Response("success");
    }
        
    public function removeFromWorkspaceAction($resourceId, $workspaceId)
    {
        $em = $this->getDoctrine()->getEntityManager();  
        $workspace = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($workspaceId);
        $managerRole = $workspace->getManagerRole();
         
        if(false === $this->get('security.context')->isGranted($managerRole->getName()))
        {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }
 
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($resourceId);
        $resourceType = $resourceInstance->getResourceType();
        $name = $this->findResService($resourceType);  
        $em->remove($resourceInstance);
        $resourceInstance->getResource()->decrInstance();
        
        if($resourceInstance->getResourceType()->getType()=='directory')
        {
            
            $this->get($name)->delete($resourceInstance);
        }
        else
        {
            if(0 == $resourceInstance->getResource()->getInstanceAmount())
            {
                $this->get($name)->delete($resourceInstance->getResource());
            }
        }

        $em->flush();
        
        return new Response("success"); 
    }
    
    public function getResourcesTypeAction()
    {
        $resourcesType = $this->getDoctrine()->getEntityManager()->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $content = $this->renderView('ClarolineCoreBundle:Resource:resource_type.json.twig', array('resourcesType' => $resourcesType));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json'); 
        
        return $response;   
    }
    
    public function getResourcesReferenceAction($instanceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);
        $resourcesInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->findBy(array('abstractResource' => $resourceInstance->getId()));
        $content = $this->renderView('ClarolineCoreBundle:Resource:resource_instance.json.twig', array('resourcesInstance' => $resourcesInstance));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json'); 
        
        return $response;
    }
    
    public function getJsonLicensesListAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $licenses = $em->getRepository('Claroline\CoreBundle\Entity\License')->findAll();
        $content = $this->renderView('ClarolineCoreBundle:Resource:license_list.json.twig', array('licenses' => $licenses));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');  
        
        return $response;
    }
    
    //the response is a test: it should be changed 
    public function getJsonPlayerListAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($id);
        $mime = $resourceInstance->getResource()->getMimeType();
        $services = $this->container->getParameter("player.service.list");
        $names = array_keys($services);
        $i = 1;
        $arrayPlayer[0][0] = 'claroline.file.manager';
        $arrayPlayer[0][1] = $this->get('claroline.file.manager')->getPlayerName();
        
        foreach($names as $name)
        {
            $srvMime = $this->get($name)->getMimeType();
            
            if($mime->getName() == $srvMime || $mime->getType() == $srvMime)
            {
                $arrayPlayer[$i][0] = $name;
                $arrayPlayer[$i][1] = $this->get($name)->getPlayerName();
                $i++;
            }
        }
        
        return new Response(var_dump($arrayPlayer));
    }
      
    private function copyByReferenceResourceInstance(ResourceInstance $resourceInstance)
    {
        $ric = new ResourceInstance();
        $ric->setUser($this->get('security.context')->getToken()->getUser());
        $ric->setCopy(true);
        $ric->setWorkspace($resourceInstance->getWorkspace());
        $ric->setResource($resourceInstance->getResource());
        $ric->setResourceType($resourceInstance->getResourceType());
        
        return $ric;
    }
    
    private function copyByCopyResourceInstance(ResourceInstance $resourceInstance)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $ric = new ResourceInstance();
        $ric->setUser($this->get('security.context')->getToken()->getUser());
        $ric->setCopy(false);
        $ric->setWorkspace($resourceInstance->getWorkspace());
        $name = $this->findResService($resourceInstance->getResourceType());
        $resourceCopy = $this->get($name)->copy($resourceInstance->getResource(), $user);
        $resourceCopy->incrInstance();
        $ric->setResource($resourceCopy);
        $ric->setResourceType($resourceInstance->getResourceType());
        
        return $ric;
    }
    
    private function setChildrenReferenceCopy($parentInstance, $workspace, $parentCopy)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $children = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($parentInstance, true);
        $rightManager = $this->get('claroline.security.right_manager');
        $roleCollaborator = $workspace->getCollaboratorRole(); 
        
        foreach($children as $child)
        {
            $copy = $this->copyByReferenceResourceInstance($child);
            $copy->setParent($parentCopy);
            $copy->setWorkspace($workspace);
            $em->persist($copy);
            $copy->getResource()->incrInstance();
            $em->flush();
            $this->setChildrenReferenceCopy($child, $workspace, $copy);
            $rightManager->addRight($copy, $roleCollaborator, MaskBuilder::MASK_VIEW);
        }
    }
    
    private function setChildrenCopyCopy($parentInstance, $workspace, $parentCopy)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $children = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($parentInstance, true);
        $rightManager = $this->get('claroline.security.right_manager');
        $roleCollaborator = $workspace->getCollaboratorRole(); 
        
        foreach($children as $child)
        {
            $copy = $this->copyByCopyResourceInstance($child);
            $copy->setParent($parentCopy);
            $copy->setWorkspace($workspace);
            $em->persist($copy);
            $em->flush();
            $this->setChildrenReferenceCopy($child, $workspace, $copy);
            $rightManager->addRight($copy, $roleCollaborator, MaskBuilder::MASK_VIEW);
        }
    }
    
    private function copyFirstReferenceInstance($workspace, $instanceId)
    {
        $em = $this->getDoctrine()->getEntityManager();  
        $roleCollaborator = $workspace->getCollaboratorRole();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);
        $resourceInstanceCopy = $this->copyByReferenceResourceInstance($resourceInstance);
        $resourceInstanceCopy->setWorkspace($workspace);
        $em->persist($resourceInstanceCopy);
        $resourceInstance->getResource()->incrInstance();
        $em->flush();     
        $user = $this->get('security.context')->getToken()->getUser();
        $rightManager = $this->get('claroline.security.right_manager');
        $rightManager->addRight($resourceInstanceCopy, $user, MaskBuilder::MASK_OWNER);
        $rightManager->addRight($resourceInstanceCopy, $roleCollaborator, MaskBuilder::MASK_VIEW);
        $this->setChildrenReferenceCopy($resourceInstance, $workspace, $resourceInstanceCopy);
    }
    
    private function copyFirstCopyInstance($workspace, $instanceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $roleCollaborator = $workspace->getCollaboratorRole();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);          
        $resourceInstanceCopy = $this->copyByCopyResourceInstance($resourceInstance);
        $resourceInstanceCopy->setWorkspace($workspace);
        $em->persist($resourceInstanceCopy);              
        $em->flush();
        $user = $this->get('security.context')->getToken()->getUser();
        $rightManager = $this->get('claroline.security.right_manager');
        $rightManager->addRight($resourceInstanceCopy, $roleCollaborator, MaskBuilder::MASK_VIEW);
        $rightManager->addRight($resourceInstanceCopy, $user, MaskBuilder::MASK_OWNER);
        $this->setChildrenCopyCopy($resourceInstance, $workspace, $resourceInstanceCopy);
    }
    
    private function findPlayerService($mime)
    {
        $services = $this->container->getParameter("player.service.list");
        $names = array_keys($services);
        $serviceName = null;
        
        foreach($names as $name)
        {
            $fileMime = $this->get($name)->getMimeType();
            $serviceName = null;
            
            if( $fileMime == $mime->getType() && $serviceName == null)
            {
                $serviceName = $name;
            }
            if($fileMime == $mime->getName() || $fileMime == $mime->getExtension())
            {
                $serviceName = $name;
            }
        }
        
        return $serviceName;
    }
     
    private function findResService(ResourceType $resourceType)
    {
        $services = $this->container->getParameter("resource.service.list");
        $names = array_keys($services);
        $serviceName = null;
        
        foreach($names as $name)
        {
            $type = $this->get($name)->getResourceType();
            
            if($type == $resourceType->getType())
            {
                $serviceName = $name;
            }
        }
        
        return $serviceName;
    }
    
}
