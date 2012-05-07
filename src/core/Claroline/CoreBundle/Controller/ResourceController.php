<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\SelectResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\Directory;
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
        $resources = $this->get('claroline.resource.manager')->getRootResourcesOfUser($user);                
        $resourcesType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findAll();

        return $this->render(
            'ClarolineCoreBundle:Resource:index.html.twig', array('form_resource' => $formResource->createView(), 'resources' => $resources, 'id' => null, 'resourcesType' => $resourcesType)
        );
    }
    
    public function showResourceFormAction($id)
    {
        $request = $this->get('request');
        $params = $request->request->all();
        $idType = $params['select_resource_form']['type'];
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
    public function addAction($type, $id)
    {
        $request = $this->get('request');
        $user = $this->get('security.context')->getToken()->getUser();
        $resourceType = $this->getDoctrine()->getEntityManager()->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => $type));
        $name = $this->findRsrcServ($resourceType);
        $form = $this->get($name)->getForm();
        $form->bindRequest($request);
 
        if($form->isValid())
        {   
            $resource = $this->get($name)->add($form, $id, $user);
            $rightManager = $this->get('claroline.security.right_manager');
            $rightManager->addRight($resource, $user, MaskBuilder::MASK_OWNER);
            
            if($request->isXmlHttpRequest())
            {
                $content = '{"key":'.$resource->getId().', "name":"'.$resource->getName().'", "type":"'.$resource->getResourceType()->getType().'"}';
                $response = new Response($content);
                $response->headers->set('Content-Type', 'application/json');  
                
                return $response;
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
              'Claroline\CoreBundle\Entity\Resource\AbstractResource')->find($id);
          
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
       $resource = $this->get('claroline.resource.manager')->find($id);
       $securityContext = $this->get('security.context');
       
       if(false == $securityContext->isGranted('OWNER', $resource))
       {
           throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
       }
       else
       {    
            $resourceType = $resource->getResourceType();
            $name = $this->findRsrcServ($resourceType);
            $this->get($name)->delete($resource);

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
       $resource = $this->get('claroline.resource.manager')->find($id);
       $securityContext = $this->get('security.context');
       
       if(false == $securityContext->isGranted('VIEW', $resource))
       {
           throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
       }
       else
       {
           $resourceType = $resource->getResourceType();
           $name = $this->findRsrcServ($resourceType);
           $response = $this->get($name)->indexAction($resource);

           return $response;
       }
    }
    
    public function getJSONResourceNodeAction($id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $response = new Response();
        
        if($id==0)
        {
            $resources = $this->get('claroline.resource.manager')->getRootResourcesOfUser($user);
            $root = new Directory();
            $root->setName('root');
            $root->setId(0);
            $em = $this->getDoctrine()->getEntityManager();
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
            $resources = $this->get('claroline.resource.manager')->find($id)->getChildren();
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
    
    public function addToWorkspaceAction($resourceId, $workspaceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId); 
        $rightManager = $this->get('claroline.security.right_manager');
        $roleCollaborator = $workspace->getCollaboratorRole();
            
        if($resourceId == 0)
        {
            $user = $this->get('security.context')->getToken()->getUser();
            $resources = $this->get('claroline.resource.manager')->getRootResourcesOfUser($user);
            
            foreach($resources as $resource)
            {
                $workspace->addResource($resource);
                $children = $resource->getChildren();
                $rightManager->addRight($resource, $roleCollaborator, MaskBuilder::MASK_VIEW);
                
                foreach($children as $child)
                {
                    $rightManager->addRight($child, $roleCollaborator, MaskBuilder::MASK_VIEW);
                }
            }           
        }
        else
        {
            $resource = $this->get('claroline.resource.manager')->find($resourceId);
            $em = $this->getDoctrine()->getEntityManager();   
            $workspace->addResource($resource);
            $children = $resource->getChildren();
            $rightManager->addRight($resource, $roleCollaborator, MaskBuilder::MASK_VIEW);
            
            foreach($children as $child)
            {
                $rightManager->addRight($child, $roleCollaborator, MaskBuilder::MASK_VIEW);
            }
        }        
        $em->flush();
        
        return new Response("success");
    }
        
    public function removeFromWorkspaceAction($resourceId, $workspaceId)
    {
        $em = $this->getDoctrine()->getEntityManager();  
        $resource = $this->get('claroline.resource.manager')->find($resourceId);
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);     
        $workspace->removeResource($resource);       
        $em->flush();
        
        return new Response("success");
        
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
}
