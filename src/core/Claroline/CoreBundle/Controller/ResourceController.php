<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\SelectResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Form\DirectoryType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;


//todo ASSERT RESSOURCE NAME NOT NULL: pq ça fonctionne pas ?
class ResourceController extends Controller
{
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser(); 
        $formResource = $this->get('form.factory')->create(new SelectResourceType(), new ResourceType());
        $resources = $this->get('claroline.resource.manager')->getRootResourcesOfUser($user);
        $em = $this->getDoctrine()->getEntityManager();        
        $resourcesType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findAll();

        return $this->render(
            'ClarolineCoreBundle:Resource:index.html.twig', array('form_resource' => $formResource->createView(), 'resources' => $resources, 'id' => null, 'resourcesType' => $resourcesType)
        );
    }
    
    public function showResourceFormAction($id)
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new SelectResourceType());
        $form->bindRequest($request);

        if ($form->isValid())
        {       
            $resourceType = $form['type']->getData();
            $rsrcServName = $this->findRsrcServ($resourceType);
            $rsrcServ = $this->get($rsrcServName);
            $form = $rsrcServ->getForm();
            
            return $this->render(
                'ClarolineCoreBundle:Resource:form_page.html.twig', array('form' => $form->createView(), 'id' => $id, 'type' => $resourceType->getType())
            );
        }
        
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
        $resourceType = $this->getDoctrine()->getEntityManager()->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => $type));
        $name = $this->findRsrcServ($resourceType);
        $form = $this->get($name)->getForm();
        $form->bindRequest($request);
 
        if($form->isValid())
        {   
            
            $user = $this->get('security.context')->getToken()->getUser();
            $resource = $this->get($name)->add($form, $id, $user);
            
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
            return $this->render(
                'ClarolineCoreBundle:Resource:form_page.html.twig', array ('form' => $form->createView(), 'type' => $type, 'id' => $id)
            );
        }
    }
    
    //pas de vérification xmlHttp; je vois pas encore comment gérer ça, je sais même pas si c'est important
    public function defaultClickAction($type, $id)
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
        
        return $response;
    }
    
    public function deleteAction($id)
    {
       $request = $this->get('request');
       $resource = $this->get('claroline.resource.manager')->find($id);
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
    
    public function openAction($id)
    {
       $resource = $this->get('claroline.resource.manager')->find($id);
       $resourceType = $resource->getResourceType();
       $name = $this->findRsrcServ($resourceType);
       $response = $this->get($name)->indexAction($resource);
       
       return $response;
    }
    
    //vieux, pas changé; utilisé à cause de dojo. Le case PUT ne sert pls à rien
    public function getJSONResourceNodeAction($id)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method){
            case 'PUT':
                return $this->PUTNode($this->getRequest()->getContent());
                break;
            default:
                return $this->GETNode($id);     
                break;
        }  
        
        throw new \Exception("ResourceController getJSONResourceNode didn't work");
    }
    
    public function getNode($id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $response = new Response();
        
        if($id==0)
        {
            $resources = $this->get('claroline.resource.manager')->getRootResourcesOfUser($user);
            $content = $this->renderView('ClarolineCoreBundle:Resource:dynatree_resource.json.twig', array('resources' => $resources));
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
        $resource = $this->get('claroline.resource.manager')->find($resourceId);
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);        
        $workspace->addResource($resource);
        $em->flush();
        
        return new Response("success");
    }
        
    public function removeFromWorkspaceAction($resourceId, $workspaceId)
    {
        $resource = $this->get('claroline.resource.manager')->find($resourceId);
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);     
        $workspace->removeResource($resource);
        $em->flush();
        
        return new Response("success");
        
    }
   
    private function findRsrcServ($resourceType)
    {
        $services = $this->container->getParameter("resource.service.list");
        $serviceName = null;
        
        foreach($services as $name => $service)
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
