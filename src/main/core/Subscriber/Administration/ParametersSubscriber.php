<?php

namespace Claroline\CoreBundle\Subscriber\Administration;

use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Manager\LocaleManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ParametersSubscriber implements EventSubscriberInterface
{
    const NAME = 'main_settings';

    /** @var ParametersSerializer */
    private $serializer;

    /** @var LocaleManager */
    private $localeManager;

    public function __construct(
        ParametersSerializer $serializer,
        LocaleManager $localeManager
    ) {
        $this->serializer = $serializer;
        $this->localeManager = $localeManager;
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
        $parameters = $this->serializer->serialize();

        $event->setData([
            'lockedParameters' => $parameters['lockedParameters'] ?? [],
            'parameters' => $parameters,
            'availableLocales' => $this->localeManager->getAvailableLocales(),
        ]);

        // do not stop event propagation to let plugins inject their own params (for example, ThemeBundle adds available icon sets and themes)
    }
}
