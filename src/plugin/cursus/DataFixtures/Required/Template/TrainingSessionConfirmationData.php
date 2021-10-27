<?php

namespace Claroline\CursusBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

class TrainingSessionConfirmationData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_session_confirmation';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Confirmation of registration for a training session',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_session_confirmation.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Confirmation d\'inscription à une session de formation',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_session_confirmation.fr.html.twig'),
                ],
            ],
        ];
    }
}
