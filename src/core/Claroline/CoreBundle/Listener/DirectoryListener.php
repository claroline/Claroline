<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Resource\ResourceEvent;
use Claroline\CoreBundle\Form\DirectoryType;
use Claroline\CoreBundle\Entity\Resource\Directory;

use Claroline\CoreBundle\Library\Resource\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Resource\CreateResourceEvent;
use Claroline\CoreBundle\Library\Resource\DeleteResourceEvent;


class DirectoryListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new DirectoryType, new Directory());
        $response = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:resource_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'directory'
            )
        );
        $event->setResponseContent($response);
        $event->stopPropagation();
    }

    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container
            ->get('form.factory')
            ->create(new DirectoryType(), new Directory());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $event->setResource($form->getData());
            $event->stopPropagation();
            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:resource_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'directory'
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }
}