<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Subscriber\Administration;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\ThemeBundle\Manager\IconSetManager;
use Claroline\ThemeBundle\Manager\ThemeManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ParametersSubscriber implements EventSubscriberInterface
{
    const NAME = 'main_settings';

    /** @var ThemeManager */
    private $themeManager;
    /** @var IconSetManager */
    private $iconSetManager;

    public function __construct(
        ThemeManager $themeManager,
        IconSetManager $iconSetManager
    ) {
        $this->themeManager = $themeManager;
        $this->iconSetManager = $iconSetManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::ADMINISTRATION, static::NAME) => 'onOpen',
        ];
    }

    /**
     * Displays parameters administration tool.
     */
    public function onOpen(OpenToolEvent $event): void
    {
        $event->setData([
            'availableThemes' => $this->themeManager->getAvailableThemes(),
            'availableIconSets' => $this->iconSetManager->getAvailableSets(),
        ]);
    }
}
