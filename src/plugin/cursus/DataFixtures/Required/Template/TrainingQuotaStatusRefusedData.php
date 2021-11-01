<?php

namespace Claroline\CursusBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

class TrainingQuotaStatusRefusedData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_quota_status_refused';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Refusing of subscription',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_quota_status_refused.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Refus de votre inscription',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_quota_status_refused.fr.html.twig'),
                ],
            ],
        ];
    }
}
