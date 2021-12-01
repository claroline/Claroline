<?php

namespace Claroline\CursusBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class TrainingSessionInvitationData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_session_invitation';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Invitation to a training session',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_session_invitation.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Invitation à une session de formation',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_session_invitation.fr.html.twig'),
                ],
            ],
        ];
    }
}
