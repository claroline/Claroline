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
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\LinkBundle\Entity\Resource\Shortcut;

/**
 * Integrates the "Shortcut" resource.
 */
class ShortcutListener
{
    /** @var SerializerProvider */
    private $serializer;

    public function __construct(
        SerializerProvider $serializer
    ) {
        $this->serializer = $serializer;
    }

    public function load(LoadResourceEvent $event)
    {
        /** @var Shortcut $shortcut */
        $shortcut = $event->getResource();

        $event->setData([
            'shortcut' => $this->serializer->serialize($shortcut),
        ]);
    }
}
