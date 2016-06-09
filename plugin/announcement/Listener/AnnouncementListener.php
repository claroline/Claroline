<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Listener;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Symfony\Component\Form\FormFactory;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Form\ResourceNameType;

/**
 * @DI\Service()
 */
class AnnouncementListener
{
    private $formFactory;
    private $httpKernel;
    private $om;
    private $request;
    private $resourceManager;
    private $router;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "httpKernel"         = @DI\Inject("http_kernel"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"       = @DI\Inject("request_stack"),
     *     "resourceManager"    = @DI\Inject("claroline.manager.resource_manager"),
     *     "router"             = @DI\Inject("router"),
     *     "templating"         = @DI\Inject("templating")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        ObjectManager $om,
        RequestStack $requestStack,
        ResourceManager $resourceManager,
        TwigEngine $templating,
        UrlGeneratorInterface $router
    ) {
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->resourceManager = $resourceManager;
        $this->router = $router;
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("create_form_claroline_announcement_aggregate")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new ResourceNameType(), new AnnouncementAggregate());
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'claroline_announcement_aggregate',
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_announcement_aggregate")
     *
     * @param CreateResourceEvent $event
     *
     * @throws \Claroline\CoreBundle\Listener\NoHttpRequestException
     */
    public function onCreate(CreateResourceEvent $event)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }

        $form = $this->formFactory->create(new ResourceNameType(), new AnnouncementAggregate());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $announcementAggregate = $form->getData();
            $event->setResources(array($announcementAggregate));
            $event->stopPropagation();

            return;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'claroline_announcement_aggregate',
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_announcement_aggregate")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        //$this->resourceManager->delete($event->getResource());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_claroline_announcement_aggregate")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineAnnouncementBundle:Announcement:announcementsList';
        $params['aggregateId'] = $event->getResource()->getId();
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_announcement_aggregate")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $aggregate = $event->getResource();
        $copy = new AnnouncementAggregate();
        $this->om->persist($copy);
        $announcements = $aggregate->getAnnouncements();

        foreach ($announcements as $announcement) {
            $newAnnouncement = new Announcement();
            $newAnnouncement->setAggregate($copy);
            $newAnnouncement->setAnnouncer($announcement->getAnnouncer());
            $newAnnouncement->setContent($announcement->getContent());
            $newAnnouncement->setCreationDate($announcement->getCreationDate());
            $newAnnouncement->setCreator($announcement->getCreator());
            $newAnnouncement->setPublicationDate($announcement->getPublicationDate());
            $newAnnouncement->setTitle($announcement->getTitle());
            $newAnnouncement->setVisible($announcement->isVisible());
            $newAnnouncement->setVisibleFrom($announcement->getVisibleFrom());
            $newAnnouncement->setVisibleUntil($announcement->getVisibleUntil());
            $this->om->persist($newAnnouncement);
        }

        $event->setCopy($copy);
        $event->stopPropagation();
    }
}
