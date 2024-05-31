<?php

namespace Claroline\CoreBundle\Subscriber\Crud;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TemplateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly TemplateManager $templateManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::POST_DELETE, Template::class) => 'postDelete',
        ];
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var Template $template */
        $template = $event->getObject();
        if ($template->getType()->getDefaultTemplate() === $template->getName()) {
            // we are deleting the default template for the type, we need to replace it by another one
            // set default template to the system one
            $newDefault = $this->om->getRepository(Template::class)->findOneBy([
                'type' => $template->getType(),
                'system' => true,
            ]);

            if (empty($newDefault)) {
                // fallback to any of the defined template if no system (should not be possible)
                $templates = $this->om->getRepository(Template::class)->findBy([
                    'type' => $template->getType(),
                ]);

                if (!empty($templates)) {
                    $newDefault = $templates[0];
                }
            }

            if (!empty($newDefault)) {
                $this->templateManager->defineTemplateAsDefault($newDefault);
            }
        }
    }
}
