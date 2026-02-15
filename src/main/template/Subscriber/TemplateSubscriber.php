<?php

namespace Claroline\TemplateBundle\Subscriber;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\TemplateBundle\Entity\Template;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TemplateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, Template::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::PRE_UPDATE, Template::class) => 'preUpdate',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var Template $template */
        $template = $event->getObject();
        if ($template->isDefault()) {
            $this->resetDefault($template->getType());
        }
    }

    public function preUpdate(UpdateEvent $event): void
    {
        /** @var Template $template */
        $template = $event->getObject();
        $oldData = $event->getOldData();

        if ($template->isDefault() && !$oldData['default']) {
            $this->resetDefault($template->getType());
        }
    }

    private function resetDefault(string $type): void
    {
        // replace old default
        $oldDefault = $this->om->getRepository(Template::class)->findOneBy([
            'type' => $type,
            'default' => true,
        ]);

        if ($oldDefault) {
            $oldDefault->setDefault(false);
            $this->om->persist($oldDefault);
        }
    }
}
