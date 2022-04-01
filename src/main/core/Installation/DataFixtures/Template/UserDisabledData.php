<?php

namespace Claroline\CoreBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class UserDisabledData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'user_disabled';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Your %platform_name% account is disabled',
                    'content' => $this->twig->render('@ClarolineCore/template/user_disabled.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Votre compte %platform_name% est désactivé',
                    'content' => $this->twig->render('@ClarolineCore/template/user_disabled.fr.html.twig'),
                ],
            ],
        ];
    }
}
