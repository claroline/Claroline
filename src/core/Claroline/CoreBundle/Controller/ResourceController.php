<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\SelectResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\Repository;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\DirectoryType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class ResourceController extends Controller
{
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser(); 
        $em = $this->getDoctrine()->getEntityManager();
        $formResource = $this->get('form.factory')->create(new SelectResourceType(), new ResourceType());
        $personnalWS = $user->getPersonnalWorkspace();
        $resources = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->getWSListableRootResource($personnalWS); 
        $resourcesType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findAll();

        return $this->render(
            'ClarolineCoreBundle:Resource:index.html.twig', array('form_resource' => $formResource->createView(), 'resources' => $resources, 'id' => null, 'resourcesType' => $resourcesType)
        );
    }
    
    public function showResourceFormAction($id)
    {
        $request = $this->get('request');
        $form = $request->request->get('select_resource_form');
        $idType = $form['type'];
        $em = $this->getDoctrine()->getEntityManager();
        $resourceType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->find($idType);
        $rsrcServName = $this->findRsrcServ($resourceType);
        $rsrcServ = $this->get($rsrcServName);
        $form = $rsrcServ->getForm();

        return $this->render(
            'ClarolineCoreBundle:Resource:form_page.html.twig', array('form' => $form->createView(), 'id' => $id, 'type' => $resourceType->getType())
        );
              
        throw new \Exception("form error");
    }
    
        
    public function getFormAction($id, $type)
    {
        $resourceType = $this->getDoctrine()->getEntityManager()->getRepository("Claroline\CoreBundle\Entity\Resource\ResourceType")->findOneBy(array('type' => $type));
        $name = $this->findRsrcServ($resourceType);
        $rsrcServ = $this->get($name);
        $form = $rsrcServ->getForm();
        
        return $this->render(
            'ClarolineCoreBundle:Resource:generic_form.html.twig', array('form' => $form->createView(), 'id' => $id, 'type' =>$type)
        );
    }
    
    //TODO: check return type; la partie js doit savoir si on retourne du json ou pas pour réafficher (ou non le formulaire)
    //TODO: renommer idRepository en idWorkspace
    public function addAction($type, $id, $idRepository)
    {
        $request = $this->get('request');
        $user = $this->get('security.context')->getToken()->getUser();
        
        if(null == $idRepository)
        {
            $idRepository = $user->getPersonnalWorkspace()->getId();
        }
        
        $resourceType = $this->getDoctrine()->getEntityManager()->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => $type));
        $name = $this->findRsrcServ($resourceType);
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
                $ri->setResourceType($resourceType);        
                $rightManager = $this->get('claroline.security.right_manager');
                $ri->setCopy(false);  
                $workspace = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($idRepository);
                $ri->setWorkspace($workspace);
                $ri->setResource($resource);
                $em->persist($ri);
                $em->flush();
                $rightManager->addRight($ri, $user, MaskBuilder::MASK_OWNER);  
                
                if($request->isXmlHttpRequest())
                {
                    $content = '{"key":'.$resource->getId().', "name":"'.$resource->getName().'", "type":"'.$resource->getResourceType()->getType().'"}';
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
    
    public function defaultClickAction($type, $id)
    {
          $resource = $this->getDoctrine()->getEntityManager()->getRepository(
              'Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($id);
          
          $securityContext = $this->get('security.context');
          
          if(false == $securityContext->isGranted('VIEW', $resource))
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
                      'Claroline\CoreBundle\Entity\Resource\AbstractResource')->find($id)->getResourceType();
              }
              $name = $this->findRsrcServ($resourceType);
              $response = $this->get($name)->getDefaultAction($id);
          }

        return $response;
    }
    
    public function deleteAction($id)
    {
       $request = $this->get('request');
       $em = $this->getDoctrine()->getEntityManager();
       $resourceInstance = $em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($id);
       $securityContext = $this->get('security.context');
       
       if(false == $securityContext->isGranted('OWNER', $resourceInstance))
       {
           throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
       }
       else
       {    
            $resourceType = $resourceInstance->getResourceType();
            $name = $this->findRsrcServ($resourceType);
            $this->get($name)->delete($resourceInstance);
            
            if($request->isXmlHttpRequest())
            {
                return new Response("delete");
            }

            $route = $this->get('router')->generate("claro_resource_index");

            return new RedirectResponse($route);
       }
    }
    
    public function openAction($id)
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
           $name = $this->findRsrcServ($resourceType);
           $response = $this->get($name)->indexAction($resourceInstance);

           return $response;
       }
    }
    
    public function editAction($idResource, $idWorkspace, $options)
    {
        //resource copy
        if($options == 'copy')
        {
            $em = $this->getDoctrine()->getEntityManager();
            $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->find($idResource);;
            $newResource = $this->createResourceCopy($resource);
            $workspace = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($idWorkspace);
            $repository = $workspace->getRepository();
            $repository->addResource($newResource);
            $newResource->setCopy(true);
            //acls
            $roleCollaborator = $workspace->getCollaboratorRole();
            $rightManager = $this->get('claroline.security.right_manager');
            $rightManager->addRight($newResource, $roleCollaborator, MaskBuilder::MASK_VIEW);
            //remove right on the old resource
            $repository->removeResource($resource);
            $repository->removeResource($resource);
            $rightManager->removeRight($resource, $roleCollaborator, MaskBuilder::MASK_VIEW);
            $em->flush();
            
            return new Response("copied");
        }
        else
        {
            //do sthg else
        }
        
        return new Response("shouldn't go there");
        
    }
    
    public function getJSONResourceNodeAction($id, $idRepository)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('Claroline\CoreBundle\Entity\Resource\Repository')->find($idRepository);
        $response = new Response();
              
        if($id==0)
        {
            $resources = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->getRepositoryListableRootResource($repository);
            $root = new Directory();
            $root->setName('root');
            $root->setId(0);
            $directoryType = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findBy(array('type' => 'directory'));
            $root->setResourceType($directoryType[0]);
            
            foreach($resources as $resource)
            {
                $root->addChildren($resource);
            }
            //must add root in an array for the json response
            $root = array( 0 => $root);
            
            $content = $this->renderView('ClarolineCoreBundle:Resource:dynatree_resource.json.twig', array('resources' => $root));
            $response = new Response($content);
            $response->headers->set('Content-Type', 'application/json');      
        }
        else
        {
            $parent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->find($id);
            $resources = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->getRepositoryListableChildren($repository, $parent);
            //ne fait plus que récupérer les resources du repository
            $content = $this->renderView('ClarolineCoreBundle:Resource:dynatree_resource.json.twig', array('resources' => $resources));
            $response = new Response($content);
            $response->headers->set('Content-Type', 'application/json');     
        }
        
        return $response;
    }
       
    public function moveResourceAction ($idChild, $idParent)
    {
        $parent = $this->get('claroline.resource.manager')->find($idParent);
        $child = $this->get('claroline.resource.manager')->find($idChild);
        $child->setParent($parent);
        $this->getDoctrine()->getEntityManager()->flush();
        
        return new Response("success");
    }
    
    public function addToWorkspaceAction($resourceId, $workspaceId, $option)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId); 
        $rightManager = $this->get('claroline.security.right_manager');
        $roleCollaborator = $workspace->getCollaboratorRole();
        
        if($option == 'ref' )
        {
            if($resourceId == 0)
            {/*
                $user = $this->get('security.context')->getToken()->getUser();
                $resources = $this->get('claroline.resource.manager')->getRootResourcesOfUser($user);

                foreach($resources as $resource)
                {
                    $workspace->addResource($resource);
                    $children = $resource->getChildren();
                    $rightManager->addRight($resource, $roleCollaborator, MaskBuilder::MASK_VIEW);

                    foreach($children as $child)
                    {
                        $workspace->addResource($child);
                        $rightManager->addRight($child, $roleCollaborator, MaskBuilder::MASK_VIEW);
                    }
                }    */       
            }
            else
            {/*
                $resource = $this->get('claroline.resource.manager')->find($resourceId);
                $em = $this->getDoctrine()->getEntityManager();   
                $repository = $workspace->getRepository();
                $repository->addResource($resource);
                $children = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->children($resource, false);
                $rightManager->addRight($resource, $roleCollaborator, MaskBuilder::MASK_VIEW);

                foreach($children as $child)
                {
                    $rightManager->addRight($child, $roleCollaborator, MaskBuilder::MASK_VIEW);
                    $repository->addResource($child);
                }*/
            }        
            $em->flush();
        }
        else
        {
           if($resourceId == 0)
           {
               
           }
           else
           {/*
               $resource = $this->get('claroline.resource.manager')->find($resourceId);
               $newResource = $this->createResourceCopy($resource, $workspace);
               $user = $this->get('security.context')->getToken()->getUser();
               $newResource->setCopy(true);
               $repository = $workspace->getRepository();
               $repository->addResource($newResource);     
               $rightManager->addRight($newResource, $user, MaskBuilder::MASK_OWNER);
               $rightManager->addRight($newResource, $roleCollaborator, MaskBuilder::MASK_VIEW);
               $children = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->children($newResource, false);
                      
               foreach($children as $child)
               {
                   $rightManager->addRight($child, $roleCollaborator, MaskBuilder::MASK_VIEW);
                   $rightManager->addRight($newResource, $user, MaskBuilder::MASK_OWNER);
                   $repository->addResource($child);
               } 
               */
               $em->flush();
           }
           return new Response("you're not trying to copy this are you ?"); 
        }
        
        return new Response("success");
    }
        
    public function removeFromWorkspaceAction($resourceId, $workspaceId)
    {/*
        $em = $this->getDoctrine()->getEntityManager();  
        $resource = $this->get('claroline.resource.manager')->find($resourceId);
        if($resource->getCopy()==false)
        {
            $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId); 
            $repository = $workspace->getRepository();
            $repository->removeResource($resource);
        }
        else
        {
            $resourceType = $resource->getResourceType();
            $name = $this->findRsrcServ($resourceType);
            $this->get($name)->delete($resource); 
        }
        $em->flush();
        
        return new Response("success"); */
    }
   
    private function findRsrcServ($resourceType)
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
    
    public function getResourcesTypeAction()
    {
        $resourcesType = $this->getDoctrine()->getEntityManager()->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $content = $this->renderView('ClarolineCoreBundle:Resource:resource_type.json.twig', array('resourcesType' => $resourcesType));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json'); 
        
        return $response;   
    }
    
    public function createResourceCopy($resource)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $resourceType = $resource->getResourceType();
        $name = $this->findRsrcServ($resourceType);
        $newResource = $this->get($name)->copy($resource, $user);
        
        return $newResource;
    }
}
