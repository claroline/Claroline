<?php

namespace Claroline\TransferBundle\Subscriber\Tool;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\TransferBundle\Transfer\ExportProvider;
use Claroline\TransferBundle\Transfer\ImportProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Transfer tool.
 */
class TransferToolSubscriber implements EventSubscriberInterface
{
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
            'administration_tool_transfer' => 'onDisplayTool',
            'open_tool_workspace_transfer' => 'onDisplayWorkspace',
        ];
    }

    /**
     * Displays transfer tool in administration.
     */
    public function onDisplayTool(OpenToolEvent $event)
    {
        $event->setData([
            'import' => [
                'explanation' => $this->importProvider->getAvailableActions('csv'),
                'samples' => $this->importProvider->getSamples('csv'),
            ],
            'export' => [
                'explanation' => $this->exportProvider->getAvailableActions('csv'),
            ],
        ]);
        $event->stopPropagation();
    }

    /**
     * Displays transfer tool on Workspace.
     */
    public function onDisplayWorkspace(OpenToolEvent $event)
    {
        $options = [Options::WORKSPACE_IMPORT];
        $extra = [
            'workspace' => $this->serializer->serialize($event->getWorkspace(), [Options::SERIALIZE_MINIMAL]),
        ];

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
