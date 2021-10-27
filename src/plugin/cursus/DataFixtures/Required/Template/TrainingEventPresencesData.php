<?php

namespace Claroline\CursusBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

class TrainingEventPresencesData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_event_presences';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Attendance grid for a training event',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_event_presences.en.pdf.twig'),
                ],
                'fr' => [
                    'title' => 'Grille de présence d\'une séance de formation',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_event_presences.fr.pdf.twig'),
                ],
            ],
        ];
    }
}
