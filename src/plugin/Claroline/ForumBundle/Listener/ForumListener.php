<?php

namespace Claroline\ForumBundle\Listener;

use Claroline\CoreBundle\Library\Plugin\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Library\Resource\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\OpenResourceEvent;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Form\ForumOptionsType;
use Claroline\ForumBundle\Form\ForumType;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ForumListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new ForumType, new Forum());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'claroline_forum'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new ForumType, new Forum());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $forum = $form->getData();
            $event->setResource($forum);
            $event->stopPropagation();
            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'claroline_forum'
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    public function onOpen(OpenResourceEvent $event)
    {
        $route = $this->container->get('router')->generate('claro_forum_open', array('resourceId' => $event->getResource()->getId()));
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    public function onAdministrate(PluginOptionsEvent $event)
    {
        $forumOptions = $this->container->get('doctrine.orm.entity_manager')->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $form = $this->container->get('form.factory')->create(new ForumOptionsType, $forumOptions[0]);
        $content = $this->container->get('templating')->render(
            'ClarolineForumBundle::plugin_options_form.html.twig', array(
            'form' => $form->createView()
            )
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $event->stopPropagation();
    }
}