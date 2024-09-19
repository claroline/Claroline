<?php

namespace Claroline\CursusBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class TrainingSessionCanceledData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_session_canceled';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Training session cancellation',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_session_canceled.en.pdf.twig'),
                ],
                'fr' => [
                    'title' => 'Annulation de session de formation',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_session_canceled.fr.pdf.twig'),
                ],
            ],
        ];
    }
}
