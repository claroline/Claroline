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

use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceLifecycleManager;
use Claroline\LinkBundle\Entity\Resource\Shortcut;

/**
 * Integrates the "Shortcut" resource.
 */
class ShortcutListener
{
    /** @var ResourceLifecycleManager */
    private $resourceLifecycle;

    /**
     * ShortcutListener constructor.
     *
     * @param ResourceLifecycleManager $resourceLifecycleManager
     */
    public function __construct(
        ResourceLifecycleManager $resourceLifecycleManager
    ) {
        $this->resourceLifecycle = $resourceLifecycleManager;
    }

    /**
     * Loads a shortcut.
     * It forwards the event to the target of the shortcut.
     *
     * @param LoadResourceEvent $event
     */
    public function load(LoadResourceEvent $event)
    {
        /** @var Shortcut $shortcut */
        $shortcut = $event->getResource();

        $targetEvent = $this->resourceLifecycle->load($shortcut->getTarget());

        $event->setData($targetEvent->getData());
    }

    /**
     * Opens a shortcut.
     * It forwards the event to the target of the shortcut.
     *
     * @param OpenResourceEvent $event
     */
    public function open(OpenResourceEvent $event)
    {
        /** @var Shortcut $shortcut */
        $shortcut = $event->getResource();

        $targetEvent = $this->resourceLifecycle->open($shortcut->getTarget());

        $event->setData($targetEvent->getData());
    }

    /**
     * Exports a shortcut.
     * It forwards the event to the target of the shortcut.
     *
     * @param DownloadResourceEvent $event
     */
    public function export(DownloadResourceEvent $event)
    {
        /** @var Shortcut $shortcut */
        $shortcut = $event->getResource();

        $targetEvent = $this->resourceLifecycle->export($shortcut->getTarget());

        $event->setData($targetEvent->getData());
    }

    /**
     * Removes a shortcut.
     *
     * @param DeleteResourceEvent $event
     */
    public function delete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }
}
