<?php

namespace Claroline\CursusBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

class TrainingSessionData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_session';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Training session',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_session.en.pdf.twig'),
                ],
                'fr' => [
                    'title' => 'Session de formation',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_session.fr.pdf.twig'),
                ],
            ],
        ];
    }
}
