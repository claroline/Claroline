<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Listener\Resource;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Form\ResourceNameType;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service
 */
class DropzoneListener
{
    /** @var DropzoneManager */
    private $dropzoneManager;
    /** @var FormFactory */
    private $formFactory;
    /** @var HttpKernelInterface */
    private $httpKernel;
    /** @var Request */
    private $request;
    /** @var TwigEngine */
    private $templating;

    /**
     * DropzoneListener constructor.
     *
     * @DI\InjectParams({
     *     "dropzoneManager"       = @DI\Inject("claroline.manager.dropzone_manager"),
     *     "formFactory"           = @DI\Inject("form.factory"),
     *     "httpKernel"            = @DI\Inject("http_kernel"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "templating"            = @DI\Inject("templating")
     * })
     *
     * @param DropzoneManager     $dropzoneManager
     * @param FormFactory         $formFactory
     * @param HttpKernelInterface $httpKernel
     * @param RequestStack        $requestStack
     * @param TwigEngine          $templating
     */
    public function __construct(
        DropzoneManager $dropzoneManager,
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        TwigEngine $templating
    ) {
        $this->dropzoneManager = $dropzoneManager;
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("create_form_claroline_dropzone")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreationForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new ResourceNameType(true), new Dropzone());
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'claroline_dropzone',
            ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_dropzone")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(new ResourceNameType(true), new Dropzone());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $published = $form->get('published')->getData();
            $event->setPublished($published);
            $dropzone = $form->getData();
            $event->setResources([$dropzone]);
            $event->stopPropagation();
        } else {
            $content = $this->templating->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig',
                [
                    'form' => $form->createView(),
                    'resourceType' => 'claroline_dropzone',
                ]
            );
            $event->setErrorFormContent($content);
            $event->stopPropagation();
        }
    }

    /**
     * @DI\Observe("open_claroline_dropzone")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $params = [];
        $params['_controller'] = 'ClarolineDropZoneBundle:Resource\Dropzone:open';
        $params['dropzone'] = $event->getResource()->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_dropzone")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $dropzone = $event->getResource();
        $copy = $this->dropzoneManager->copyDropzone($dropzone);

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_dropzone")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->dropzoneManager->delete($event->getResource());
        $event->stopPropagation();
    }
}
