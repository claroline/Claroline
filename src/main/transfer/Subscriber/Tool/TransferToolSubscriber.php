<?php

namespace Claroline\TransferBundle\Subscriber\Tool;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\TransferBundle\Transfer\ExportProvider;
use Claroline\TransferBundle\Transfer\ImportProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Transfer tool.
 */
class TransferToolSubscriber implements EventSubscriberInterface
{
    const NAME = 'transfer';

    /** @var ImportProvider */
    private $importProvider;
    /** @var ExportProvider */
    private $exportProvider;
    /** @var SerializerProvider */
    private $serializer;

    public function __construct(
        ImportProvider $transfer,
        ExportProvider $exportProvider,
        SerializerProvider $serializer
    ) {
        $this->importProvider = $transfer;
        $this->exportProvider = $exportProvider;
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::WORKSPACE, static::NAME) => 'onOpen',
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::DESKTOP, static::NAME) => 'onOpen',
        ];
    }

    public function onOpen(OpenToolEvent $event)
    {
        $options = [];
        $extra = [];
        if (Tool::WORKSPACE === $event->getContext()) {
            $options[] = Options::WORKSPACE_IMPORT;
            $extra['workspace'] = $this->serializer->serialize($event->getWorkspace(), [Options::SERIALIZE_MINIMAL]);
        }

        $event->setData([
            'import' => [
                'explanation' => $this->importProvider->getAvailableActions('csv', $options, $extra),
                'samples' => $this->importProvider->getSamples('csv', $options, $extra),
            ],
            'export' => [
                'explanation' => $this->exportProvider->getAvailableActions('csv', $options, $extra),
            ],
        ]);

        $event->stopPropagation();
    }
}
