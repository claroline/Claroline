<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Resource;

use Symfony\Component\HttpFoundation\Response;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Observe;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceActivity;
use Claroline\CoreBundle\Form\ActivityType;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Event\ImportResourceTemplateEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;

/**
 * @Service
 */
class ActivityListener
{
    private $formFactory;
    private $templating;
    private $request;
    private $persistence;
    private $entityManager;
    private $activityManager;

    /**
     * @InjectParams({
     *     "formFactory"        = @Inject("form.factory"),
     *     "templating"         = @Inject("templating"),
     *     "request"            = @Inject("request_stack"),
     *     "persistence"        = @Inject("claroline.persistence.object_manager"),
     *     "entityManager"      = @Inject("doctrine.orm.entity_manager"),
     *     "activityManager"    = @Inject("claroline.manager.activity_manager"),
     * })
     */
    public function __construct($formFactory, $templating, $request, $persistence, $entityManager, $activityManager)
    {
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->request = $request->getMasterRequest();
        $this->persistence = $persistence;
        $this->entityManager = $entityManager;
        $this->activityManager = $activityManager;
    }

    /**
     * @Observe("create_form_activity")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new ActivityType, new Activity());
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'activity'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @Observe("create_activity")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $formData = $this->request->get('activity_form');
        $form = $this->formFactory->create(new ActivityType(), $formData);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $data = $form->getData();
            $activity = $this->activityManager->createActivity(
                $data['name'], $data['description'], $data['resourceNode'], false
            );

            $event->setResources(array($activity));
            $event->stopPropagation();

            return;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'activity'
            )
        );

        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @Observe("delete_activity")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * @Observe("copy_activity")
     *
     * @todo: Do the resources need to be copied ?
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        /*$resource = $event->getResource();
        $copy = new Activity();
        $copy->setInstructions($resource->getInstructions());
        $resourceActivities = $resource->getResourceActivities();

        foreach ($resourceActivities as $resourceActivity) {
            $ra = new ResourceActivity();
            $ra->setResourceNode($resourceActivity->getResourceNode());
            $ra->setSequenceOrder($resourceActivity->getSequenceOrder());
            $ra->setActivity($copy);
            $this->persistence->persist($ra);
        }

        $this->persistence->persist($copy);
        $event->setCopy($copy);
        $event->stopPropagation();*/
    }

    /**
     * @Observe("resource_activity_to_template")
     *
     * @param ExportResourceTemplateEvent $event
     */
    public function onExportTemplate(ExportResourceTemplateEvent $event)
    {
        /*$resource = $event->getResource();
        $config['instructions'] = $resource->getInstructions();
        $resourceActivities = $this->entityManager->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findResourceActivities($resource);
        $resourceDependencies = array();

        foreach ($resourceActivities as $resourceActivity) {
            if ($resourceActivity->getResourceNode()->getWorkspace() ===
                $resource->getResourceNode()->getWorkspace()) {
                $resourceActivityConfig['id'] = $resourceActivity->getResourceNode()->getId();
                $resourceActivityConfig['order'] = $resourceActivity->getSequenceOrder();
                $config['resources'][] = $resourceActivityConfig;
            }
        }

        $event->setFiles($resourceDependencies);
        $event->setConfig($config);
        $event->stopPropagation();*/
    }

    /**
     * @Observe("resource_activity_from_template")
     *
     * @param ImportResourceTemplateEvent $event
     */
    public function onImportTemplate(ImportResourceTemplateEvent $event)
    {
        /*$config = $event->getConfig();
        $activity = new Activity();
        $activity->setInstructions($config['instructions']);

        foreach ($config['resources'] as $data) {
            $resourceActivity = new ResourceActivity();
            $resourceActivity->setResource($event->find($data['id']));
            $resourceActivity->setSequenceOrder($data['order']);
            $resourceActivity->setActivity($activity);
            $this->persistence->persist($resourceActivity);
        }

        $event->setResource($activity);
        $event->stopPropagation();*/
    }

    /**
     * @Observe("open_activity")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        /*$activity = $event->getResource();
        $content = $this->templating->render(
            'ClarolineCoreBundle:Activity/player:activity.html.twig',
            array('activity' => $activity)
        );

        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();*/
    }

    /**
     * @Observe("compose_activity")
     */
    public function onCompose(CustomActionResourceEvent $event)
    {
        /*$resourceTypes = $this->entityManager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $activity = $event->getResource();
        $resourceActivities = $this->entityManager->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findResourceActivities($activity);

        $content = $this->templating->render(
            'ClarolineCoreBundle:Activity:index.html.twig',
            array(
                'resourceTypes' => $resourceTypes,
                'resourceActivities' => $resourceActivities,
                '_resource' => $activity
            )
        );

        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();*/
    }
}
