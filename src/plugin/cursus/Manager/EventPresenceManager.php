<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\EventPresence;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventPresenceManager
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ObjectManager $om,
        private readonly TemplateManager $templateManager
    ) {
    }

    public function generate(Event $event, array $eventUsers): array
    {
        /** @var EventPresence[] $existingPresences */
        $existingPresences = $this->om->getRepository(EventPresence::class)->findBy(['event' => $event]);

        $presences = [];
        foreach ($eventUsers as $user) {
            $presence = null;
            foreach ($existingPresences as $index => $existingPresence) {
                if ($existingPresence->getUser()->getId() === $user->getId()) {
                    $presence = $existingPresence;
                    // speed up next searches by removing current presence
                    array_splice($existingPresences, $index, 1);
                    break;
                }
            }

            if (empty($presence)) {
                $presence = new EventPresence();
                $presence->setUser($user);
                $presence->setEvent($event);
                $presence->setStatus(EventPresence::UNKNOWN);

                $this->om->persist($presence);
            }

            $presences[] = $presence;
        }

        $this->om->flush();

        return $presences;
    }

    public function removePresence(Event $event, $user): void
    {
        $presence = $this->om->getRepository(EventPresence::class)->findOneBy([
            'event' => $event,
            'user' => $user,
            'status' => EventPresence::UNKNOWN, // we only remove empty Presence to keep event history
        ]);

        if ($presence) {
            $this->om->remove($presence);
            $this->om->flush();
        }
    }

    public function setStatus(array $presences, string $status): array
    {
        foreach ($presences as $presence) {
            $presence->setStatus($status);
            $this->om->persist($presence);
        }

        $this->om->flush();

        return $presences;
    }

    public function download(Event $event, array $users, string $locale, bool $filled = false): string
    {
        $presences = $this->generate($event, $users);

        // sort presence by name
        usort($presences, function (EventPresence $a, EventPresence $b) {
            if ($a->getUser()->getLastName() === $b->getUser()->getLastName()) {
                return 0;
            }

            return ($a->getUser()->getLastName() < $b->getUser()->getLastName()) ? -1 : 1;
        });

        $table = '<table style="border: 1px solid black; width: 100%; border-collapse: collapse;">';
        foreach ($presences as $presence) {
            $table .= '<tr>';
            $table .= "<td style='border: 1px solid black; width: 75%; padding: 10px;'>{$presence->getUser()->getLastName()} {$presence->getUser()->getFirstName()}</td>";
            if ($filled) {
                $table .= "<td style='border: 1px solid black; padding: 10px;'>{$this->translator->trans('presence_'.$presence->getStatus(), [], 'cursus')}</td>";
            } else {
                $table .= '<td style="border: 1px solid black; padding: 10px;">&nbsp;</td>';
            }
            $table .= '</tr>';
        }
        $table .= '</table>';

        $placeholders = array_merge([
                'event_name' => $event->getName(),
                'event_code' => $event->getCode(),
                'event_description' => $event->getDescription(),
                'event_presences_table' => $table,
            ],
            $this->templateManager->formatDatePlaceholder('event_start', $event->getStartDate()),
            $this->templateManager->formatDatePlaceholder('event_end', $event->getEndDate()),
        );

        return $this->templateManager->getTemplate('training_event_presences', $placeholders, $locale);
    }

    public function downloadUser(EventPresence $presence, string $locale): string
    {
        $event = $presence->getEvent();
        $user = $presence->getUser();
        $status = $presence->getStatus();

        $placeholders = array_merge([
                'event_name' => $event->getName(),
                'event_code' => $event->getCode(),
                'event_description' => $event->getDescription(),
                'event_presence_status' => $this->translator->trans('presence_'.$status, [], 'cursus'),
                'user_username' => $user->getUsername(),
                'user_first_name' => $user->getFirstName(),
                'user_last_name' => $user->getLastName(),
            ],
            $this->templateManager->formatDatePlaceholder('event_start', $event->getStartDate()),
            $this->templateManager->formatDatePlaceholder('event_end', $event->getEndDate()),
        );

        if ($event->getPresenceTemplate()) {
            return $this->templateManager->getTemplateContent($event->getPresenceTemplate(), $placeholders, $locale);
        }

        // use the default template
        return $this->templateManager->getTemplate('training_event_presence', $placeholders, $locale);
    }
}
