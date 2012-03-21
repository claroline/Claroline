<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Form\ChooseResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

class ResourceController extends Controller
{
    public function indexAction()
    {
       /* $resourceManager = $this->get('claroline.resource.manager');
        $resources = $resourceManager->findAll();       
        */
    }
    
    public function getChooseFormAction()
    {
        $formResource = $this->get('form.factory')->create(new ChooseResourceType(), new ResourceType());

        return $this->render(
            'ClarolineCoreBundle:Resource:create.html.twig', array('form_resource' => $formResource->createView()/*, 'resources' => $resources*/)
        );
    }
    
    public function chooseAction()
    {
        $request = $this->get('request');
        $form = $this->get('form.factory')->create(new ChooseResourceType());
        $form->bindRequest($request);

        if ($form->isValid())
        {
            $resourceType = $form['type']->getData();
         
            $routeName = $this->get('claroline.routing')->getRouteName($resourceType->getBundle(), $resourceType->getController(), 'index');
            $route = $this->get('router')->generate($routeName);
            
            return new RedirectResponse($route);
        }
        
        throw new \Exception("form error");
    }
}
