<?php

namespace Claroline\CursusBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class TrainingQuotaSubscriptionCreated extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'training_quota_subscription_created';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Subscription created for validation',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_quota_subscription_created.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Une nouvelle inscription doit être validée',
                    'content' => $this->twig->render('@ClarolineCursus/template/training_quota_subscription_created.fr.html.twig'),
                ],
            ],
        ];
    }
}
