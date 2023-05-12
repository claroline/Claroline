<?php

namespace YourNamespace\YourBundle\Subscriber;

use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PrivacySubscriber implements EventSubscriberInterface
{
    const NAME = 'privacy';

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::ADMINISTRATION, static::NAME) => 'onOpen',
        ];
    }


    /* pas certaine pour cette partie de code
    -----------------------------------------------------------
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function onOpen(OpenToolEvent $event): void
    {
        $jsonFilePath = $this->projectDir . '/files/config/platform_options.json';

        if (!file_exists($jsonFilePath)) {
            throw new NotFoundHttpException("Les données n'ont pas été trouvé.");
        }

        $jsonData = json_decode(file_get_contents($jsonFilePath), true);

        $event->setData([
            'platform_options' => $jsonData,
        ]);
    }
    */
}
