<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceActivity;
use Claroline\CoreBundle\Form\ActivityType;
use Claroline\CoreBundle\Library\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Event\OpenResourceEvent;
use Claroline\CoreBundle\Library\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\DeleteResourceEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

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

    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resource = $event->getResource();
        $copy = new Activity();
        $copy->setInstructions($resource->getInstructions());
        $resourceActivities = $resource->getResourceActivities();

        foreach ($resourceActivities as $resourceActivity) {
            $ra = new ResourceActivity();
            $ra->setResource($resourceActivity->getResource());
            $ra->setSequenceOrder($resourceActivity->getSequenceOrder());
            $ra->setActivity($copy);
            $em->persist($ra);
        }

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    public function onExportTemplate(ExportResourceTemplateEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resource = $event->getResource();
        $config['instructions'] = $resource->getInstructions();
        $resourceActivities = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findResourceActivities($resource);
        $resourceDependencies = array();

        foreach ($resourceActivities as $resourceActivity) {
            if ($resourceActivity->getResource()->getWorkspace() === $resource->getWorkspace()) {
                $resourceActivityConfig['id'] = $resourceActivity->getResource()->getId();
                $resourceActivityConfig['order'] = $resourceActivity->getSequenceOrder();
                $config['resources'][] = $resourceActivityConfig;
            }
        }

        $event->setResourceDependencies($resourceDependencies);
        $event->setConfig($config);
        $event->stopPropagation();
    }

    /**
     * If the activity is recursive, it may or may not work depending on where
     * the activity is defined in the config file.
     *
     * @param \Claroline\CoreBundle\Library\Event\ImportResourceTemplateEvent $event
     */
    public function onImportTemplate(ImportResourceTemplateEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $config = $event->getConfig();
        $activity = new Activity();
        $activity->setInstructions($config['instructions']);

        foreach ($config['resources'] as $data) {
            $resourceActivity = new ResourceActivity();
            $resourceActivity->setResource($event->find($data['id']));
            $resourceActivity->setSequenceOrder($data['order']);
            $resourceActivity->setActivity($activity);
            $em->persist($resourceActivity);
        }

        $event->setResource($activity);
        $event->stopPropagation();
    }

    public function onOpen(OpenResourceEvent $event)
    {
        $resourceTypes = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findBy(array('isVisible' => true));
        $activity = $event->getResource();
        $resourceActivities = $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findResourceActivities($activity);

        if ($this->container->get('security.context')->getToken()->getUser() == $activity->getCreator()) {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Activity:index.html.twig',
                array(
                    'resourceTypes' => $resourceTypes,
                    'activity' => $activity,
                    'workspace' => $activity->getWorkspace(),
                    'resourceActivities' => $resourceActivities
                )
            );
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Activity/player:activity.html.twig',
                array(
                    'activity' => $activity,
                    'resource' => $resourceActivities[0]->getResource()
                )
            );
        }

        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
