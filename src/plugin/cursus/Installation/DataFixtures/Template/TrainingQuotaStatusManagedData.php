<?php

namespace Claroline\CursusBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class TrainingQuotaStatusManagedData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_quota_status_managed';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Validation of subscription',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_quota_status_managed.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Validation de votre inscription',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_quota_status_managed.fr.html.twig'),
                ],
            ],
        ];
    }
}
