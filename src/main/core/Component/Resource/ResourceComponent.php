<?php

namespace Claroline\CoreBundle\Component\Resource;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\EmbedResourceEvent;
use Claroline\CoreBundle\Event\Resource\ExportResourceEvent;
use Claroline\CoreBundle\Event\Resource\ImportResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\UpdateResourceEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class ResourceComponent implements ResourceInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        $resourceEvents = [
            // Read
            ResourceEvents::getEventName(ResourceEvents::OPEN, static::getName()) => 'onOpen',
            ResourceEvents::getEventName(ResourceEvents::EMBED, static::getName()) => 'onEmbed',
            // Update
            ResourceEvents::getEventName(ResourceEvents::CREATE, static::getName()) => 'onCreate',
            ResourceEvents::getEventName(ResourceEvents::UPDATE, static::getName()) => 'onUpdate',
            ResourceEvents::getEventName(ResourceEvents::COPY, static::getName()) => 'onCopy',
            ResourceEvents::getEventName(ResourceEvents::DELETE, static::getName()) => 'onDelete',
            // Transfer
            ResourceEvents::getEventName(ResourceEvents::EXPORT, static::getName()) => 'onExport',
            ResourceEvents::getEventName(ResourceEvents::IMPORT, static::getName()) => 'onImport',
        ];

        if (class_implements(static::class, DownloadableResourceInterface::class)) {
            $resourceEvents[ResourceEvents::getEventName(ResourceEvents::DOWNLOAD, static::getName())] = 'onDownload';
        }

        return $resourceEvents;
    }

    public function onOpen(LoadResourceEvent $event): void
    {
        $event->setData(
            $this->open($event->getResource(), $event->isEmbedded()) ?? []
        );
    }

    public function onEmbed(EmbedResourceEvent $event): void
    {
        /*$event->setData(
            $this->embed($event->getResource())
        );*/
    }

    public function onDownload(DownloadResourceEvent $event): void
    {
        $downloadableFilepath = $this->download($event->getResource());

        // not all resources implement the download behavior
        if ($downloadableFilepath) {
            $event->setItem($downloadableFilepath);
        }
    }

    public function onCreate(CreateResourceEvent $event): void
    {
        /*$this->create($event->getResource(), $event->getData());*/
    }

    public function onUpdate(UpdateResourceEvent $event): void
    {
        $event->addResponse(
            $this->update($event->getResource(), $event->getData()) ?? []
        );
    }

    public function onCopy(CopyResourceEvent $event): void
    {
        $this->copy($event->getResource(), $event->getCopy());
    }

    public function onExport(ExportResourceEvent $event): void
    {
        $event->setData(
            $this->export($event->getResource(), $event->getFileBag()) ?? []
        );
    }

    public function onImport(ImportResourceEvent $event): void
    {
        $this->import($event->getResource(), $event->getFileBag(), $event->getData());
    }

    public function onDelete(DeleteResourceEvent $event): void
    {
        $fileBag = new FileBag();

        $delete = $this->delete($event->getResource(), $fileBag, $event->isSoftDelete());
        if (!$delete) {
            $event->enableSoftDelete();
        }

        $event->setFiles($fileBag->all());
    }

    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        return [];
    }

    public function download(AbstractResource $resource): ?string
    {
        return null;
    }

    public function update(AbstractResource $resource, array $data): ?array
    {
        return [];
    }

    public function delete(AbstractResource $resource, FileBag $fileBag, bool $softDelete = true): bool
    {
        return true;
    }

    public function copy(AbstractResource $original, AbstractResource $copy): void
    {
    }

    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        return [];
    }

    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void
    {
    }
}
