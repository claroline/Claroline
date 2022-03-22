<?php

namespace Claroline\LogBundle\Subscriber\Functional;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Messenger\Message\CreateFunctionalLog;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class GroupLogSubscriber implements EventSubscriberInterface
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
            Crud::getEventName('patch', 'post', User::class) => 'logGroupChanges', // add a Group to a User
            Crud::getEventName('patch', 'post', Group::class) => 'logGroupChanges', // add a User to a Group
        ];
    }

    public function logGroupChanges(PatchEvent $event)
    {
        $object = $event->getObject();
        $value = $event->getValue();
        $action = $event->getAction();

        $group = null;
        $user = null;
        if ($object instanceof Group) {
            $group = $object;
            $user = $value;
        } elseif ($value instanceof Group) {
            $group = $value;
            $user = $object;
        }

        if (!$group || !$user) {
            return;
        }

        if (Crud::COLLECTION_ADD === $action) {
            $this->messageBus->dispatch(new CreateFunctionalLog(
                new \DateTime(),
                'group_add',
                $this->translator->trans('group_add_desc', [
                    '%group%' => $group,
                    '%user%' => $user,
                ], 'log'),
                $this->security->getUser()->getId()
            ));
        } elseif (Crud::COLLECTION_REMOVE === $action) {
            $this->messageBus->dispatch(new CreateFunctionalLog(
                new \DateTime(),
                'group_remove',
                $this->translator->trans('group_remove_desc', [
                    '%group%' => $group,
                    '%user%' => $user,
                ], 'log'),
                $this->security->getUser()->getId()
            ));
        }
    }
}
