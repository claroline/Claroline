<?php

namespace Innova\PathBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;

use Innova\PathBundle\Entity\Path;

class PathListener extends ContainerAware
{

    public function onPathOpen(OpenResourceEvent $event)
    {
        $path = $event->getResource();
        $route = $this->container
            ->get('router')
            ->generate(
                'innova_path_player_index',
                array(
                    'workspaceId' => $path->getResourceNode()->getWorkspace()->getId(),
                    'pathId' => $path->getId(),
                    'stepId' => $path->getRootStep()->getId()
                )
            );
        
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    public function onPathCreate(CreateResourceEvent $event)
    {
        // Create form
        $form = $this->container->get('form.factory')->create('innova_path', new Path());
        
        // Try to prcess form
        $request = $this->container->get('request');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $path = $form->getData();

            $properties = array (
                'name' => $path->getName(),
                'description' => $path->getDescription(),
            );

            $path->setStructure(json_encode($properties));
            $event->setResources(array ($path));
        }
        else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig',
                array(
                    'form' => $form->createView(),
                    'resourceType' => 'innova_path'
                )
            );

            $event->setErrorFormContent($content);
        }
        
        $event->stopPropagation();
    }

    public function onPathCreateForm(CreateFormResourceEvent $event)
    {
        // Create form
        $form = $this->container->get('form.factory')->create('innova_path', new Path());
        
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'innova_path'
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onPathDelete(DeleteResourceEvent $event)
    {
        
        $event->stopPropagation();
    }

    public function onPathCopy(CopyResourceEvent $event)
    {

        throw new \Exception("You can't copy path.");
    }
}
