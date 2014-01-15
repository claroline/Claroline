<?php

namespace Innova\PathBundle\Listener\Resource;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;

use Innova\PathBundle\Entity\NonDigitalResource;

class NonDigitalResourceListener extends ContainerAware
{
    
    public function onNonDigitalResourceCreateForm(CreateFormResourceEvent $event)
    {
        // Create form
        $form = $this->container->get('form.factory')->create('innova_non_digital_resource', new NonDigitalResource());
        
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'innova_non_digital_resource'
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onNonDigitalResourceCreate(CreateResourceEvent $event)
    {
        // Create form
        $form = $this->container->get('form.factory')->create('innova_non_digital_resource', new NonDigitalResource());
        
        // Try to prcess form
        $request = $this->container->get('request');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $non_digital_resource = $form->getData();
            $event->setResources(array ($non_digital_resource));
        }
        else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig',
                array(
                    'form' => $form->createView(),
                    'resourceType' => 'non_digital_resource'
                )
            );

            $event->setErrorFormContent($content);
        }
        
        $event->stopPropagation();
    }


    public function onNonDigitalResourceOpen(OpenResourceEvent $event)
    {
        $nonDigitalResource = $event->getResource();
        $route = $this->container
            ->get('router')
            ->generate(
                'innova_nondigitalresource_player',
                array(
                    'nonDigitalResourceId' => $nonDigitalResource->getId(),
                )
            );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    public function onNonDigitalResourceDelete(DeleteResourceEvent $event)
    {
        $ndr = $event->getResource();

        $em = $this->container->get('doctrine.orm.entity_manager');
        $step2resourceNodes = $em->getRepository('InnovaPathBundle:Step2ResourceNode')->findByResourceNode($ndr->getResourceNode());

        if(count($step2resourceNodes) > 0){
            throw new \Exception('The resource you want to delete is used.');
           }
    }

    public function onNonDigitalResourceCopy(CopyResourceEvent $event)
    {
        $originalResource = $event->getResource();

        $resourceCopy = new NonDigitalResource();
        $resourceCopy->setNonDigitalResourceType($originalResource->getNonDigitalResourceType());
        $resourceCopy->setDescription($originalResource->getDescription());

        $event->setCopy($resourceCopy);
    }
}
