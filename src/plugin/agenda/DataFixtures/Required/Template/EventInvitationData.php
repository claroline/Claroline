<?php

namespace Claroline\AgendaBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

class EventInvitationData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'event_invitation';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Invitation to an event',
                    'content' => $this->twig->render('@ClarolineAgenda/template/event_invitation.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Invitation à un évènement',
                    'content' => $this->twig->render('@ClarolineAgenda/template/event_invitation.fr.html.twig'),
                ],
            ],
        ];
    }
}
