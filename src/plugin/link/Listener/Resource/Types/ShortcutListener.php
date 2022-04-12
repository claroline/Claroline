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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ImportResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceLifecycleManager;
use Claroline\LinkBundle\Entity\Resource\Shortcut;

/**
 * Integrates the "Shortcut" resource.
 */
class ShortcutListener
{
    /** @var SerializerProvider */
    private $serializer;
    /** @var ResourceLifecycleManager */
    private $resourceLifecycle;

    public function __construct(
        SerializerProvider $serializer,
        ResourceLifecycleManager $resourceLifecycleManager
    ) {
        $this->serializer = $serializer;
        $this->resourceLifecycle = $resourceLifecycleManager;
    }

    /**
     * Loads a shortcut.
     */
    public function load(LoadResourceEvent $event)
    {
        /** @var Shortcut $shortcut */
        $shortcut = $event->getResource();

        $event->setData([
            'embedded' => $this->serializer->serialize($shortcut->getTarget(), [Options::SERIALIZE_MINIMAL]),
        ]);
    }

    /**
     * Exports a shortcut.
     * It forwards the event to the target of the shortcut.
     */
    public function download(DownloadResourceEvent $event)
    {
        /** @var Shortcut $shortcut */
        $shortcut = $event->getResource();

        $targetEvent = $this->resourceLifecycle->export($shortcut->getTarget());

        $event->setItem($targetEvent->getItem());
    }

    public function onImport(ImportResourceEvent $event)
    {
        // TODO : implement
    }
}
