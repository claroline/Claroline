<?php

namespace Claroline\AgendaBundle\Messenger;

use Claroline\AgendaBundle\Entity\EventInvitation;
use Claroline\AgendaBundle\Messenger\Message\SendEventInvitation;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Send an invitation to an event to a user.
 */
class SendEventInvitationHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RouterInterface $router,
        private readonly ObjectManager $om,
        private readonly PlatformManager $platformManager,
        private readonly TemplateManager $templateManager
    ) {
    }

    public function __invoke(SendEventInvitation $sendEventInvitation): void
    {
        /** @var EventInvitation $invitation */
        $invitation = $this->om->getRepository(EventInvitation::class)->find($sendEventInvitation->getInvitationId());
        if ($invitation) {
            $user = $invitation->getUser();
            $event = $invitation->getEvent();
            $locale = $user->getLocale();

            $location = $event->getLocation();
            $locationName = '';
            $locationAddress = '';

            if ($location) {
                $locationName = $location->getName();
                $locationAddress = $location->getAddress();
                if ($location->getPhone()) {
                    $locationAddress .= '<br>'.$location->getPhone();
                }
            }

            $placeholders = array_merge([
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'username' => $user->getUsername(),

                    // event info
                    'event_name' => $event->getName(),
                    'event_description' => $event->getDescription(),
                    'event_poster' => $event->getPoster() ? '<img src="'.$this->platformManager->getUrl().'/'.$event->getPoster().'" style="max-width: 100%;"/>' : '',
                    'event_location_name' => $locationName,
                    'event_location_address' => $locationAddress,

                    // set status urls
                    'event_join_url' => $this->router->generate(
                        'apiv2_event_change_invitation_status',
                        ['id' => $invitation->getId(), 'status' => EventInvitation::JOIN],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    'event_maybe_url' => $this->router->generate(
                        'apiv2_event_change_invitation_status',
                        ['id' => $invitation->getId(), 'status' => EventInvitation::MAYBE],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    'event_decline_url' => $this->router->generate(
                        'apiv2_event_change_invitation_status',
                        ['id' => $invitation->getId(), 'status' => EventInvitation::RESIGN],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                ],
                $this->templateManager->formatDatePlaceholder('event_start', $event->getStartDate()),
                $this->templateManager->formatDatePlaceholder('event_end', $event->getEndDate())
            );

            if ($event->getInvitationTemplate()) {
                // use custom template
                $title = $this->templateManager->getTemplateContent($event->getInvitationTemplate(), $placeholders, $locale, 'title');
                $content = $this->templateManager->getTemplateContent($event->getInvitationTemplate(), $placeholders, $locale);
            } else {
                // use default template
                $title = $this->templateManager->getTemplate('event_invitation', $placeholders, $locale, 'title');
                $content = $this->templateManager->getTemplate('event_invitation', $placeholders, $locale);
            }

            $event = new SendMessageEvent(
                $content,
                $title,
                [$user],
                $event->getCreator(),
                [
                    ['name' => 'invitation.ics', 'type' => 'text/calendar', 'url' => $sendEventInvitation->getICSPath()],
                ]
            );

            $this->eventDispatcher->dispatch($event, MessageEvents::MESSAGE_SENDING);
        }
    }
}
