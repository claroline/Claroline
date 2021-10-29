<?php

namespace Claroline\CursusBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

class TrainingEventPresenceData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_event_presence';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Certificate of attendance to a training event',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_event_presence.en.pdf.twig'),
                ],
                'fr' => [
                    'title' => 'Attestation de présence à une séance de formation',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_event_presence.fr.pdf.twig'),
                ],
            ],
        ];
    }
}
