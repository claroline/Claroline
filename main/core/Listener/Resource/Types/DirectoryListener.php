<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Resource\Types;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Form\DirectoryType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Integrates the "Directory" resource.
 *
 * @DI\Service
 */
class DirectoryListener
{
    /** @var ContainerInterface */
    private $container;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var TwigEngine */
    private $templating;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * DirectoryListener constructor.
     *
     * @DI\InjectParams({
     *     "container"   = @DI\Inject("service_container"),
     *     "formFactory" = @DI\Inject("form.factory"),
     *     "templating"  = @DI\Inject("templating"),
     *     "serializer"  = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param ContainerInterface   $container
     * @param FormFactoryInterface $formFactory
     * @param TwigEngine           $templating
     * @param SerializerProvider   $serializer
     */
    public function __construct(
        ContainerInterface $container,
        FormFactoryInterface $formFactory,
        TwigEngine $templating,
        SerializerProvider $serializer
    ) {
        $this->container = $container;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->serializer = $serializer;
    }

    /**
     * @DI\Observe("resource.directory.create")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * Loads a directory.
     *
     * @DI\Observe("load_directory")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        $event->setAdditionalData([
            'directory' => $this->serializer->serialize($event->getResource()),
        ]);

        $event->stopPropagation();
    }

    /**
     * Opens a directory.
     *
     * @DI\Observe("open_directory")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $directory = $event->getResource();
        $content = $this->templating->render(
            'ClarolineCoreBundle:Directory:index.html.twig',
            [
                'directory' => $directory,
                '_resource' => $directory,
            ]
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * Removes a directory.
     *
     * @DI\Observe("delete_directory")
     *
     * @param deleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * Copies a directory.
     *
     * @DI\Observe("copy_directory")
     *
     * @param copyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $resourceCopy = new Directory();
        $event->setCopy($resourceCopy);
    }
}
