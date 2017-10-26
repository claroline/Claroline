<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Listener\Resource;

use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Form\ResourceNameType;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service()
 */
class AnnouncementListener
{
    /** @var FormFactory */
    private $formFactory;
    /** @var HttpKernelInterface */
    private $httpKernel;
    /** @var ObjectManager */
    private $om;
    /** @var Request */
    private $request;
    /** @var TwigEngine */
    private $templating;
    /** @var AnnouncementManager */
    private $manager;

    /**
     * AnnouncementListener constructor.
     *
     * @DI\InjectParams({
     *     "formFactory"  = @DI\Inject("form.factory"),
     *     "httpKernel"   = @DI\Inject("http_kernel"),
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "templating"   = @DI\Inject("templating"),
     *     "manager"      = @DI\Inject("claroline.manager.announcement_manager")
     * })
     *
     * @param FormFactory         $formFactory
     * @param HttpKernelInterface $httpKernel
     * @param ObjectManager       $om
     * @param RequestStack        $requestStack
     * @param TwigEngine          $templating
     * @param AnnouncementManager $manager
     */
    public function __construct(
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        ObjectManager $om,
        RequestStack $requestStack,
        TwigEngine $templating,
        AnnouncementManager $manager
    ) {
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
        $this->manager = $manager;
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
            'ClarolineCoreBundle:Resource:createForm.html.twig', [
                'form' => $form->createView(),
                'resourceType' => 'claroline_announcement_aggregate',
            ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_announcement_aggregate")
     *
     * @param CreateResourceEvent $event
     *
     * @throws NoHttpRequestException
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
            $event->setResources([$announcementAggregate]);
            $event->stopPropagation();

            return;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig', [
                'form' => $form->createView(),
                'resourceType' => 'claroline_announcement_aggregate',
            ]
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
        /** @var AnnouncementAggregate $aggregate */
        $aggregate = $event->getResource();
        $announcements = $aggregate->getAnnouncements();

        if ($announcements) {
            foreach ($announcements as $announcement) {
                $this->manager->delete($announcement, false);
            }
        }
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_claroline_announcement_aggregate")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineAnnouncementBundle:Announcement:open.html.twig', [
                '_resource' => $event->getResource(),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_announcement_aggregate")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        /** @var AnnouncementAggregate $aggregate */
        $aggregate = $event->getResource();

        $this->om->startFlushSuite();
        $copy = new AnnouncementAggregate();
        $this->om->persist($copy);

        $announcements = $aggregate->getAnnouncements();
        foreach ($announcements as $announcement) {
            $newAnnouncement = $this->manager->serialize($announcement);
            $this->manager->create($copy, $newAnnouncement, false);
        }
        $this->om->endFlushSuite();

        $event->setCopy($copy);
        $event->stopPropagation();
    }
}
