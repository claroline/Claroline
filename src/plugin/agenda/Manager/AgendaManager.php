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
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaManager
{
    private $om;
    private $tokenStorage;
    private $authorization;
    private $rm;
    private $translator;
    private $container;
    private $projectDir;
    private $dispatcher;

    public function __construct(
        ObjectManager $om,
        string $projectDir,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        RoleManager $rm,
        TranslatorInterface $translator,
        ContainerInterface $container,
        StrictDispatcher $dispatcher
    ) {
        $this->projectDir = $projectDir;
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->rm = $rm;
        $this->translator = $translator;
        $this->container = $container;
        $this->dispatcher = $dispatcher;
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

        $creator = $this->tokenStorage->getToken()->getUser();

        $this->dispatcher->dispatch(
            MessageEvents::MESSAGE_SENDING,
            SendMessageEvent::class,
            [
                $this->translator->trans('send_message_content', [
                    '%Sender%' => $creator->getUserName(),
                    '%Start%' => $event->getStart(),
                    '%End%' => $event->getEnd(),
                    '%Description%' => $event->getDescription(),
                    '%JoinAction%' => $this->container->get('router')->generate(
                        'claro_agenda_invitation_action',
                        ['event' => $event->getId(), 'action' => EventInvitation::JOIN],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    '%MaybeAction%' => $this->container->get('router')->generate(
                        'claro_agenda_invitation_action',
                        ['event' => $event->getId(), 'action' => EventInvitation::MAYBE],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    '%ResignAction%' => $this->container->get('router')->generate(
                        'claro_agenda_invitation_action',
                        ['event' => $event->getId(), 'action' => EventInvitation::RESIGN],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                ], 'agenda'),
                $this->translator->trans('send_message_object', ['%EventName%' => $event->getTitle()], 'agenda'),
                [$users],
                $creator,
                false,
            ]
        );
    }

    public function checkOpenAccess(Workspace $workspace)
    {
        if (!$this->authorization->isGranted('agenda', $workspace)) {
            throw new AccessDeniedException('You cannot open the agenda');
        }
    }

    public function checkEditAccess(Workspace $workspace)
    {
        if (!$this->authorization->isGranted(['agenda', 'edit'], $workspace)) {
            throw new AccessDeniedException('You cannot edit the agenda');
        }
    }

    /**
     * Find every Event for a given user and the replace him by another.
     *
     * @return int
     */
    public function replaceEventUser(User $from, User $to)
    {
        $events = $this->om->getRepository('ClarolineAgendaBundle:Event')->findBy(['user' => $from]);

        if (count($events) > 0) {
            foreach ($events as $event) {
                $event->setUser($to);
            }

            $this->om->flush();
        }

        return count($events);
    }

    /**
     * Find every EventInvitation for a given user and the replace him by another.
     *
     * @return int
     */
    public function replaceEventInvitationUser(User $from, User $to)
    {
        $eventInvitations = $this->om->getRepository('ClarolineAgendaBundle:EventInvitation')->findByUser($from);

        if (count($eventInvitations) > 0) {
            foreach ($eventInvitations as $eventInvitation) {
                $eventInvitation->setUser($to);
            }

            $this->om->flush();
        }

        return count($eventInvitations);
    }
}
