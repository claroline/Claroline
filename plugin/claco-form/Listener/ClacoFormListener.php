<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Listener;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Form\ResourceNameType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service
 */
class ClacoFormListener
{
    private $clacoFormManager;
    private $formFactory;
    private $httpKernel;
    private $om;
    private $platformConfigHandler;
    private $request;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "clacoFormManager"      = @DI\Inject("claroline.manager.claco_form_manager"),
     *     "formFactory"           = @DI\Inject("form.factory"),
     *     "httpKernel"            = @DI\Inject("http_kernel"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "templating"            = @DI\Inject("templating")
     * })
     */
    public function __construct(
        ClacoFormManager $clacoFormManager,
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        ObjectManager $om,
        PlatformConfigurationHandler $platformConfigHandler,
        RequestStack $requestStack,
        TwigEngine $templating
    ) {
        $this->clacoFormManager = $clacoFormManager;
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->om = $om;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("create_form_claroline_claco_form")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreationForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new ResourceNameType(true), new ClacoForm());
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'claroline_claco_form',
            ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_claco_form")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(new ResourceNameType(true), new ClacoForm());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $published = $form->get('published')->getData();
            $event->setPublished($published);
            $clacoForm = $this->clacoFormManager->initializeClacoForm($form->getData());
            $event->setResources([$clacoForm]);
            $event->stopPropagation();
        } else {
            $content = $this->templating->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig',
                [
                    'form' => $form->createView(),
                    'resourceType' => 'claroline_claco_form',
                ]
            );
            $event->setErrorFormContent($content);
            $event->stopPropagation();
        }
    }

    /**
     * @DI\Observe("open_claroline_claco_form")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $params = [];
        $params['_controller'] = 'ClarolineClacoFormBundle:ClacoForm:clacoFormOpen';
        $params['clacoForm'] = $event->getResource()->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_claco_form")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $clacoForm = $event->getResource();
        $newNode = $event->getCopiedNode();
        $copy = $this->clacoFormManager->copyClacoForm($clacoForm, $newNode);

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_claco_form")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->om->remove($event->getResource());
        $event->stopPropagation();
    }
}
