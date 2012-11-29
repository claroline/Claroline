<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Form\ActivityType;
use Claroline\CoreBundle\Library\Resource\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\OpenResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\ExportResourceEvent;
use Symfony\Component\DependencyInjection\ContainerAware;

class ActivityListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new ActivityType, new Activity());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'activity'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container
            ->get('form.factory')
            ->create(new ActivityType(), new Activity());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $event->setResource($form->getData());
            $event->stopPropagation();
            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'activity'
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    // TODO : add error handling (exceptions)
    public function onDelete(DeleteResourceEvent $event)
    {

    }

    public function onCopy(CopyResourceEvent $event)
    {

    }

    public function onExport(ExportResourceEvent $event)
    {

    }

    public function onOpen(OpenResourceEvent $event)
    {

    }
}