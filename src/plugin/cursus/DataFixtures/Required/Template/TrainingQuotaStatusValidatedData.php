<?php

namespace Claroline\CursusBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

class TrainingQuotaStatusValidatedData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_quota_status_validated';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Validation of subscription',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_quota_status_validated.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Validation de votre inscription',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_quota_status_validated.fr.html.twig'),
                ],
            ],
        ];
    }
}
