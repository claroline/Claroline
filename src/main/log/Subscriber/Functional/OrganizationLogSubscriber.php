<?php

namespace Claroline\LogBundle\Subscriber\Functional;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Messenger\Message\CreateFunctionalLog;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrganizationLogSubscriber implements EventSubscriberInterface
{
    /** @var Security */
    private $security;
    /** @var TranslatorInterface */
    private $translator;
    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(
        Security $security,
        TranslatorInterface $translator,
        MessageBusInterface $messageBus
    ) {
        $this->security = $security;
        $this->translator = $translator;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('patch', 'post', User::class) => 'logOrganizationChanges', // add an Organization to a User
            Crud::getEventName('patch', 'post', Organization::class) => 'logOrganizationChanges', // add a User to an Organization
        ];
    }

    public function logOrganizationChanges(PatchEvent $event)
    {
        $object = $event->getObject();
        $value = $event->getValue();
        $action = $event->getAction();

        $organization = null;
        $user = null;
        if ($object instanceof Organization) {
            $organization = $object;
            $user = $value;
        } elseif ($value instanceof Organization) {
            $organization = $value;
            $user = $object;
        }

        if (!$organization || !$user) {
            return;
        }

        if (Crud::COLLECTION_ADD === $action) {
            $this->messageBus->dispatch(new CreateFunctionalLog(
                new \DateTime(),
                'organization_add',
                $this->translator->trans('organization_add_desc', [
                    '%organization%' => $organization->getName(),
                    '%user%' => $user,
                ], 'log'),
                $this->security->getUser()->getId()
            ));
        } elseif (Crud::COLLECTION_REMOVE === $action) {
            $this->messageBus->dispatch(new CreateFunctionalLog(
                new \DateTime(),
                'organization_remove',
                $this->translator->trans('organization_remove_desc', [
                    '%organization%' => $organization->getName(),
                    '%user%' => $user,
                ], 'log'),
                $this->security->getUser()->getId()
            ));
        }
    }
}
