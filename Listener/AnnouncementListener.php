<?php

namespace Claroline\AnnouncementBundle\Listener;

use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
//use Claroline\CoreBundle\Event\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\Event\CreateResourceEvent;
//use Claroline\CoreBundle\Event\Event\DeleteResourceEvent;
//use Claroline\CoreBundle\Event\Event\DownloadResourceEvent;
//use Claroline\CoreBundle\Event\Event\OpenResourceEvent;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service(scope="request")
 */
class AnnouncementListener
{
    private $formFactory;
    private $request;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "formFactory"    = @DI\Inject("claroline.form.factory"),
     *     "request"        = @DI\Inject("request"),
     *     "templating"     = @DI\Inject("templating")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        Request $request,
        TwigEngine $templating
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
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
}