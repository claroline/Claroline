<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LinkBundle\Listener\Resource\Types;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\LinkBundle\Entity\Resource\Shortcut;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Integrates the "Shortcut" resource.
 *
 * @DI\Service
 */
class ShortcutListener
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
     * ShortcutListener constructor.
     *
     * @DI\InjectParams({
     *     "container"       = @DI\Inject("service_container"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "templating"      = @DI\Inject("templating"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "serializer"      = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param ContainerInterface   $container
     * @param FormFactoryInterface $formFactory
     * @param TwigEngine           $templating
     * @param StrictDispatcher     $eventDispatcher
     * @param SerializerProvider   $serializer
     */
    public function __construct(
        ContainerInterface $container,
        FormFactoryInterface $formFactory,
        TwigEngine $templating,
        StrictDispatcher $eventDispatcher,
        SerializerProvider $serializer
    ) {
        $this->container = $container;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
    }

    /**
     * Loads a shortcut.
     * It forwards the event to the target of the shortcut.
     *
     * @DI\Observe("load_resource_shortcut")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /*$shortcut = $event->getResource();
        $event->setAdditionalData([
            //'directory' => $this->serializer->serialize(),
        ]);

        $event->stopPropagation();*/
    }

    /**
     * Opens a shortcut.
     * It forwards the event to the target of the shortcut.
     *
     * @DI\Observe("resource.shortcut.open")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
    }

    /**
     * Exports a shortcut.
     * It forwards the event to the target of the shortcut.
     *
     * @DI\Observe("resource.shortcut.export")
     *
     * @param DownloadResourceEvent $event
     */
    public function onExport(DownloadResourceEvent $event)
    {
    }

    /**
     * Removes a shortcut.
     *
     * @DI\Observe("resource.shortcut.delete")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * Copies a shortcut.
     *
     * @DI\Observe("resource.shortcut.copy")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        /* @var Shortcut $shortcut */
        /*$shortcut = $event->getResource();

        $copy = new ResourceShortcut();
        $copy->setTarget($shortcut->getTarget());

        $event->setCopy($copy);*/
    }
}
