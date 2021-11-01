<?php

namespace Claroline\CursusBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

class TrainingEventData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_event';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Training event',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_event.en.pdf.twig'),
                ],
                'fr' => [
                    'title' => 'Séance de formation',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_event.fr.pdf.twig'),
                ],
            ],
        ];
    }
}
