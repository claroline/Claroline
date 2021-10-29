<?php

namespace Claroline\CursusBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

class TrainingEventInvitationData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_event_invitation';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Invitation to a training event',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_event_invitation.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Invitation à une séance de formation',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_event_invitation.fr.html.twig'),
                ],
            ],
        ];
    }
}
