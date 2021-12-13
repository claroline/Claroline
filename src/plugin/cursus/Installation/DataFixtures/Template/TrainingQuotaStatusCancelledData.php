<?php

namespace Claroline\CursusBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class TrainingQuotaStatusCancelledData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_quota_status_cancelled';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Cancellation of subscription',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_quota_status_cancelled.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Annulation de votre inscription',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_quota_status_cancelled.fr.html.twig'),
                ],
            ],
        ];
    }
}
