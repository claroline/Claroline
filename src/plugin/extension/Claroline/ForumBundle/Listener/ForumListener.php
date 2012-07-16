<?php

namespace Claroline\ForumBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Library\Resource\Event\ResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CustomActionResourceEvent;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Form\ForumType;

class ForumListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new ForumType, new Forum());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:resource_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'forum'
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
            'ClarolineCoreBundle:Resource:resource_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'forum'
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    public function onOpen(CustomActionResourceEvent $event)
    {
        $route = $this->container->get('router')->generate('claro_forum_open', array('forumId' => $event->getResourceId()));
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }
}