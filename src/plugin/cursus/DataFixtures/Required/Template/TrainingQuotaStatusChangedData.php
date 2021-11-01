<?php

namespace Claroline\CursusBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

class TrainingQuotaStatusChangedData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_quota_status_changed';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Updating of subscription status',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_quota_status_changed.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Changement de status d\'inscription',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_quota_status_changed.fr.html.twig'),
                ],
            ],
        ];
    }
}
