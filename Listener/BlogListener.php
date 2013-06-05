<?php

namespace ICAP\BlogBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateResourceEvent;
use ICAP\BlogBundle\Form;
use ICAP\BlogBundle\Entity;

class BlogListener extends ContainerAware
{
    /**
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new Form\BlogType(), new Entity\Blog());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'icap_blog'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new Form\BlogType(), new Entity\Blog());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $blog = $form->getData();
            $event->setResource($blog);
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:create_form.html.twig',
                array(
                    'form' => $form->createView(),
                    'resourceType' => 'icap_blog'
                )
            );
            $event->setErrorFormContent($content);
        }
        $event->stopPropagation();
    }
}
