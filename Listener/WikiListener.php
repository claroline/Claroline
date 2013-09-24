<?php

namespace Icap\WikiBundle\Listener;

use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\LogCreateDelegateViewEvent;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Form\WikiType;

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
                'icap_wiki_view',
                array('wikiId' => $event->getResource()->getId())
            );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $em->flush();
        $event->stopPropagation();
    }

}