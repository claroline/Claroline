<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Form\ChooseResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
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

        return $this->render(
            'ClarolineCoreBundle:Resource:index.html.twig', array('form_resource' => $formResource->createView(), 'resources' => $resources)
        );
    }
    
    public function addToDirectoryAction($id)
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new ChooseResourceType());
        $form->bindRequest($request);

        if ($form->isValid())
        {
            $resourceType = $form['type']->getData();
            $routeName = $this->get('claroline.routing')->getRouteName($resourceType->getBundle(), $resourceType->getController(), 'addToDirectory');
            $route = $this->get('router')->generate($routeName, array('id' => $id));
            
            return new RedirectResponse($route);
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
        return new Response("this resource will be removed");
    }
}
