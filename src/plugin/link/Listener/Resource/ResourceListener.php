<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LinkBundle\Listener\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\LinkBundle\Manager\ShortcutManager;

/**
 * Integrates the "Shortcut" resource.
 */
class ResourceListener
{
    /** @var ObjectManager */
    private $shortcutManager;

    /**
     * ShortcutListener constructor.
     */
    public function __construct(
        ShortcutManager $shortcutManager
    ) {
        $this->shortcutManager = $shortcutManager;
    }

    /**
     * Gets all shortcuts of a resource.
     */
    public function shortcuts(ResourceActionEvent $event)
    {
    }

    /**
     * Removes all linked shortcuts when a resource is deleted.
     */
    public function delete(ResourceActionEvent $event)
    {
        $this->shortcutManager->removeShortcutsTo($event->getResourceNode());
    }
}
