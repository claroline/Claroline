<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Manager;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AgendaBundle\Entity\EventInvitation;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaManager
{
    /** @var ObjectManager */
    private $om;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var RouterInterface */
    private $router;
    /** @var EventDispatcherInterface */
    private $dispatcher;
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        EventDispatcherInterface $dispatcher,
        TranslatorInterface $translator
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->dispatcher = $dispatcher;
        $this->translator = $translator;
    }

    public function sendInvitation(Event $event, array $users = [])
    {
        foreach ($users as $key => $user) {
            $invitation = $this->om->getRepository('ClarolineAgendaBundle:EventInvitation')->findOneBy([
                'user' => $user,
                'event' => $event,
            ]);

            if ($invitation) {
                unset($users[$key]);
                continue;
            }

            $eventInvitation = new EventInvitation($event, $user);
            $this->om->persist($eventInvitation);
        }
        $this->om->flush();

        // TODO : replace by Template
        $creator = $event->getCreator() ?? $this->tokenStorage->getToken()->getUser();
        $message = new SendMessageEvent(
            $this->translator->trans('send_message_content', [
                '%Sender%' => $creator->getUserName(),
                '%Start%' => $event->getStartDate(),
                '%End%' => $event->getEndDate(),
                '%Description%' => $event->getDescription(),
                '%JoinAction%' => $this->router->generate(
                    'claro_agenda_invitation_action',
                    ['event' => $event->getId(), 'action' => EventInvitation::JOIN],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                '%MaybeAction%' => $this->router->generate(
                    'claro_agenda_invitation_action',
                    ['event' => $event->getId(), 'action' => EventInvitation::MAYBE],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                '%ResignAction%' => $this->router->generate(
                    'claro_agenda_invitation_action',
                    ['event' => $event->getId(), 'action' => EventInvitation::RESIGN],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ], 'agenda'),
            $this->translator->trans('send_message_object', ['%EventName%' => $event->getName()], 'agenda'),
            $users,
            $creator,
            false
        );

        $this->dispatcher->dispatch($message, MessageEvents::MESSAGE_SENDING);
    }
}
