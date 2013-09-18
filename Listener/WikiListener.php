<?php

namespace ICAP\WikiBundle\Listener;

use Claroline\CoreBundle\Event\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Event\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\Event\CopyResourceEvent;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use ICAP\WikiBundle\Entity\Wiki;
use ICAP\WikiBundle\Form\WikiType;

class WikiListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new WikiType(), new Wiki());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'icap_wiki'
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
    
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new WikiType(), new Wiki());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $wiki = $form->getData();
            $event->setResources(array($wiki));
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig',
                array(
                    'form' => $form->createView(),
                    'resourceType' => 'icap_wiki'
                )
            );
            $event->setErrorFormContent($content);
        }
        $event->stopPropagation();
    }
    
    public function onOpen(OpenResourceEvent $event)
    {
        $route = $this->container
            ->get('router')
            ->generate(
                'icap_wiki_edition',
                array('resourceId' => $event->getResource()->getId())
            );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

}