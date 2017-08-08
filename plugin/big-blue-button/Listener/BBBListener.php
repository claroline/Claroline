<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BigBlueButtonBundle\Listener;

use Claroline\BigBlueButtonBundle\Entity\BBB;
use Claroline\BigBlueButtonBundle\Form\BBBType;
use Claroline\BigBlueButtonBundle\Manager\BBBManager;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service
 */
class BBBListener
{
    private $bbbManager;
    private $formFactory;
    private $httpKernel;
    private $om;
    private $platformConfigHandler;
    private $request;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "bbbManager"            = @DI\Inject("claroline.manager.bbb_manager"),
     *     "formFactory"           = @DI\Inject("form.factory"),
     *     "httpKernel"            = @DI\Inject("http_kernel"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "templating"            = @DI\Inject("templating")
     * })
     */
    public function __construct(
        BBBManager $bbbManager,
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        ObjectManager $om,
        PlatformConfigurationHandler $platformConfigHandler,
        RequestStack $requestStack,
        TwigEngine $templating
    ) {
        $this->bbbManager = $bbbManager;
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->om = $om;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("plugin_options_bigbluebuttonbundle")
     *
     * @param PluginOptionsEvent $event
     */
    public function onPluginOptionsOpen(PluginOptionsEvent $event)
    {
        $params = [];
        $params['_controller'] = 'ClarolineBigBlueButtonBundle:BBB:pluginConfigurationForm';
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_form_claroline_big_blue_button")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreationForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new BBBType(), new BBB());
        $content = $this->templating->render(
            'ClarolineBigBlueButtonBundle:BBB:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'claroline_big_blue_button',
            ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_big_blue_button")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $bbb = new BBB();
        $form = $this->formFactory->create(new BBBType(), $bbb);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $published = $form->get('published')->getData();
            $startDate = $form->get('startDate')->getData();
            $endDate = $form->get('endDate')->getData();

            if ($startDate) {
                $bbb->setStartDate(\DateTime::createFromFormat('d/m/Y H:i', $startDate));
            }
            if ($endDate) {
                $bbb->setEndDate(\DateTime::createFromFormat('d/m/Y H:i', $endDate));
            }
            $event->setPublished($published);
            $event->setResources([$bbb]);
            $event->stopPropagation();
        } else {
            $content = $this->templating->render(
                'ClarolineBigBlueButtonBundle:BBB:createForm.html.twig',
                [
                    'form' => $form->createView(),
                    'resourceType' => 'claroline_big_blue_button',
                ]
            );
            $event->setErrorFormContent($content);
            $event->stopPropagation();
        }
    }

    /**
     * @DI\Observe("open_claroline_big_blue_button")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $params = [];
        $params['_controller'] = 'ClarolineBigBlueButtonBundle:BBB:bbbOpen';
        $params['bbb'] = $event->getResource()->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_big_blue_button")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $newNode = $event->getCopiedNode();
        $copy = new BBB();
        $copy->setResourceNode($newNode);

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_big_blue_button")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->om->remove($event->getResource());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("claroline_external_agenda_events")
     *
     * @param GenericDataEvent $event
     */
    public function onAgendaEventsRequest(GenericDataEvent $event)
    {
        $events = $event->getResponse() ? $event->getResponse() : [];
        $data = $event->getData();
        $type = $data['type'];

        if ($type === 'workspace') {
            $workspace = $data['workspace'];
            $bbbList = $this->bbbManager->getBBBWithDatesByWorkspace($workspace);

            foreach ($bbbList as $bbb) {
                $events[] = [
                    'title' => $bbb->getRoomName() ? $bbb->getRoomName() : $bbb->getResourceNode()->getName(),
                    'start' => $bbb->getStartDate()->format(\DateTime::ISO8601),
                    'end' => $bbb->getEndDate()->format(\DateTime::ISO8601),
                    'description' => $bbb->getWelcomeMessage(),
                    'color' => '#243973',
                    'allDay' => false,
                    'isTask' => false,
                    'isTaskDone' => false,
                    'isEditable' => false,
                    'workspace_id' => $workspace->getId(),
                    'workspace_name' => $workspace->getName(),
                    'className' => 'pointer-hand bbb_event_'.$bbb->getId(),
                    'durationEditable' => false,
                ];
            }
        }
        $event->setResponse($events);
    }
}
