<?php

namespace Claroline\CoreBundle\Component\Resource;

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

abstract class AbstractResource implements ResourceInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // Read
            ResourceEvents::getEventName(ResourceEvents::READ, static::getName()) => 'onRead',
            ResourceEvents::getEventName(ResourceEvents::EMBED, static::getName()) => 'onEmbed',
            ResourceEvents::getEventName(ResourceEvents::DOWNLOAD, static::getName()) => 'onDownload',
            // Update
            ResourceEvents::getEventName(ResourceEvents::CREATE, static::getName()) => 'onCreate',
            ResourceEvents::getEventName(ResourceEvents::UPDATE, static::getName()) => 'onUpdate',
            ResourceEvents::getEventName(ResourceEvents::COPY, static::getName()) => 'onCopy',
            ResourceEvents::getEventName(ResourceEvents::DELETE, static::getName()) => 'onDelete',
            // Transfer
            ResourceEvents::getEventName(ResourceEvents::EXPORT, static::getName()) => 'onExport',
            ResourceEvents::getEventName(ResourceEvents::IMPORT, static::getName()) => 'onImport',
        ];
    }

    public function onRead(LoadResourceEvent $event): void
    {
        $event->setData(
            $this->read($event->getResource(), $event->isEmbedded())
        );
    }

    public function onEmbed(EmbedResourceEvent $event): void
    {
        $event->setData(
            $this->embed($event->getResource())
        );
    }

    public function onDownload(DownloadResourceEvent $event): void
    {
        $event->setItem(
            $this->download($event->getResource())
        );
    }

    public function onCreate(CreateResourceEvent $event): void
    {
        $this->create($event->getResource(), $event->getData());
    }

    public function onUpdate(UpdateResourceEvent $event): void
    {
        $this->update($event->getResource(), $event->getData());
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
        $delete = $this->delete($event->getResource(), $event->isSoftDelete());
        if (!$delete) {
            $event->enableSoftDelete();
        }
    }
}
