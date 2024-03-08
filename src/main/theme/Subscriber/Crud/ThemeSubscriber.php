<?php

namespace Claroline\ThemeBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\ThemeBundle\Entity\Theme;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ThemeSubscriber implements EventSubscriberInterface
{
    private FileManager $fileManager;

    public function __construct(
        FileManager $fileManager
    ) {
        $this->fileManager = $fileManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'post', Theme::class) => 'postCreate',
            Crud::getEventName('update', 'post', Theme::class) => 'postUpdate',
            Crud::getEventName('delete', 'post', Theme::class) => 'postDelete',
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
