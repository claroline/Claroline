<?php

namespace Claroline\ThemeBundle\Subscriber\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\ThemeBundle\Entity\Theme;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ThemeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly FileManager $fileManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Theme::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Theme::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, Theme::class) => 'postDelete',
        ];
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Theme $theme */
        $theme = $event->getObject();

        if ($theme->getLogo()) {
            $this->fileManager->linkFile(Theme::class, $theme->getUuid(), $theme->getLogo());
        }
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var Theme $theme */
        $theme = $event->getObject();
        $oldData = $event->getOldData();

        $this->fileManager->updateFile(
            Theme::class,
            $theme->getUuid(),
            $theme->getLogo(),
            !empty($oldData['logo']) ? $oldData['logo'] : null
        );
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var Theme $theme */
        $theme = $event->getObject();

        if ($theme->getLogo()) {
            $this->fileManager->unlinkFile(Theme::class, $theme->getUuid(), $theme->getLogo());
        }
    }
}
