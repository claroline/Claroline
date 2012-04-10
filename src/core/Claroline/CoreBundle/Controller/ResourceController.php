<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\ChooseResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Form\DirectoryType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ResourceController extends Controller
{
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser(); 
        $formResource = $this->get('form.factory')->create(new ChooseResourceType(), new ResourceType());
        $resources = $this->get('claroline.resource.manager')->getRootResourcesOfUser($user);
        $em = $this->getDoctrine()->getEntityManager();
        $resourcesType = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $routes = $this->get('claroline.routing')->getAllFormRoutes();

        return $this->render(
            'ClarolineCoreBundle:Resource:index.html.twig', array('form_resource' => $formResource->createView(), 'resources' => $resources, 'resourcesType' => $resourcesType, 'routes' => $routes)
        );
    }
    
    public function showResourceFormAction($id)
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new ChooseResourceType());
        $form->bindRequest($request);

        if ($form->isValid())
        {       
            $resourceType = $form['type']->getData();
            $route = $resourceType->getVendor().$resourceType->getBundle()."_".$resourceType->getType()."_add";
            $rsrcServName = $resourceType->getService();
            $rsrcServ = $this->get($rsrcServName);
            $form = $rsrcServ->getForm();
            
            return $this->render(
                'ClarolineCoreBundle:Resource:form_page.html.twig', array('form' => $form->createView(), 'route' => $route, 'id' => $id)
            );
        }
        
        throw new \Exception("form error");
    }
    
    public function viewAction($id)
    {
       $resource = $this->get('claroline.resource.manager')->find($id);
       $resourceType = $resource->getResourceType();
       
       $routeName = $this->get('claroline.routing')->getRouteName($resourceType->getBundle(), $resourceType->getController(), 'view');
       $route = $this->get('router')->generate($routeName, array('id' => $id));
            
       return new RedirectResponse($route);
    }
    
    public function deleteAction($id)
    {
       $resource = $this->get('claroline.resource.manager')->find($id);
       $resourceType = $resource->getResourceType();
       $routeName = $this->get('claroline.routing')->getRouteName($resourceType->getBundle(), $resourceType->getController(), 'delete');
       $route = $this->get('router')->generate($routeName, array('id' => $id));
            
       return new RedirectResponse($route);
    }

    //todo: changer la réponse twig (pour un seul niveau)
    public function getJSONResourceNodeAction($id)
    {
        if($id==0)
        {
            $user = $this->get('security.context')->getToken()->getUser(); 
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
        }
        else
        {
            $root = $this->get('claroline.resource.manager')->find($id);
            $em = $this->getDoctrine()->getEntityManager();
        }
        $content = $this->renderView( 'ClarolineCoreBundle:Resource:resource.json.twig', array('root' => $root));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    public function editAction($param)
    {
        return new Response ($param);
    }
}
