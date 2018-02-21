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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Form\DirectoryType;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @DI\Service
 */
class DirectoryListener
{
    private $container;
    private $roleManager;
    private $resourceManager;
    private $rightsManager;
    private $maskManager;
    private $eventDispatcher;
    private $formFactory;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "templating"      = @DI\Inject("templating"),
     *     "container"       = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        RoleManager $roleManager,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        MaskManager $maskManager,
        StrictDispatcher $eventDispatcher,
        FormFactoryInterface $formFactory,
        TwigEngine $templating,
        ContainerInterface $container
    ) {
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->container = $container;
        $this->maskManager = $maskManager;
    }

    /**
     * @DI\Observe("create_form_directory")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new DirectoryType(), new Directory());
        $response = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'directory',
            ]
        );
        $event->setResponseContent($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_directory")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->formFactory->create(new DirectoryType(), new Directory());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $published = $form->get('published')->getData();
            $event->setPublished($published);
            $event->setResources([$form->getData()]);
            $event->stopPropagation();

            return;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'directory',
            ]
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_directory")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $dir = $event->getResourceNode();
        $file = $this->resourceManager->download([$dir]);
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=archive');
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Connection', 'close');

        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_directory")
     *
     * @param deleteResourceEvent $event
     *
     * Removes a directory
     */
    public function delete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_directory")
     *
     * @param copyResourceEvent $event
     *
     * Copy a directory
     */
    public function copy(CopyResourceEvent $event)
    {
        $resourceCopy = new Directory();
        $event->setCopy($resourceCopy);
    }
}
