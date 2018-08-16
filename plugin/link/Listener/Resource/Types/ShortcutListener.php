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

use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceLifecycleManager;
use Claroline\LinkBundle\Entity\Resource\Shortcut;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Integrates the "Shortcut" resource.
 *
 * @DI\Service
 */
class ShortcutListener
{
    /** @var ResourceLifecycleManager */
    private $resourceLifecycle;

    /**
     * ShortcutListener constructor.
     *
     * @DI\InjectParams({
     *     "resourceLifecycleManager" = @DI\Inject("claroline.manager.resource_lifecycle")
     * })
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
     * @DI\Observe("resource.shortcut.load")
     *
     * @param LoadResourceEvent $event
     */
    public function load(LoadResourceEvent $event)
    {
        /** @var Shortcut $shortcut */
        $shortcut = $event->getResource();

        $this->resourceLifecycle->load($shortcut->getTarget());
    }

    /**
     * Opens a shortcut.
     * It forwards the event to the target of the shortcut.
     *
     * @DI\Observe("resource.shortcut.open")
     *
     * @param OpenResourceEvent $event
     */
    public function open(OpenResourceEvent $event)
    {
        /** @var Shortcut $shortcut */
        $shortcut = $event->getResource();

        $this->resourceLifecycle->open($shortcut->getTarget());
    }

    /**
     * Exports a shortcut.
     * It forwards the event to the target of the shortcut.
     *
     * @DI\Observe("resource.shortcut.export")
     *
     * @param DownloadResourceEvent $event
     */
    public function export(DownloadResourceEvent $event)
    {
        /** @var Shortcut $shortcut */
        $shortcut = $event->getResource();

        $this->resourceLifecycle->export($shortcut->getTarget());
    }

    /**
     * Removes a shortcut.
     *
     * @DI\Observe("resource.shortcut.delete")
     *
     * @param DeleteResourceEvent $event
     */
    public function delete(DeleteResourceEvent $event)
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
    public function copy(CopyResourceEvent $event)
    {
        // TODO : implement
    }
}
