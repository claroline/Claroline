<?php

namespace Claroline\AnnouncementBundle\Listener;

use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
//use Claroline\CoreBundle\Event\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\Event\DeleteResourceEvent;
//use Claroline\CoreBundle\Event\Event\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Event\OpenResourceEvent;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service(scope="request")
 */
class AnnouncementListener
{
    private $formFactory;
    private $request;
    private $resourceManager;
    private $router;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "request"            = @DI\Inject("request"),
     *     "resourceManager"    = @DI\Inject("claroline.manager.resource_manager"),
     *     "router"             = @DI\Inject("router"),
     *     "templating"         = @DI\Inject("templating")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        Request $request,
        ResourceManager $resourceManager,
        TwigEngine $templating,
        UrlGeneratorInterface $router
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
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
        $form = $this->formFactory->create(
            FormFactory::TYPE_RESOURCE_RENAME,
            array(),
            new AnnouncementAggregate()
        );
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'claroline_announcement_aggregate'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_announcement_aggregate")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(
            FormFactory::TYPE_RESOURCE_RENAME,
            array(),
            new AnnouncementAggregate()
        );
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
                'resourceType' => 'claroline_announcement_aggregate'
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
//        $this->resourceManager->delete($event->getResource());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_claroline_announcement_aggregate")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $route = $this->router->generate(
            'claro_announcements_list',
            array('aggregateId' => $event->getResource()->getId())
        );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }
}