<?php

namespace Claroline\CoreBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class UserActivationData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'user_activation';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Activate your %platform_name% account',
                    'content' => $this->twig->render('@ClarolineCore/template/user_activation.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Activation de votre compte %platform_name%',
                    'content' => $this->twig->render('@ClarolineCore/template/user_activation.fr.html.twig'),
                ],
            ],
        ];
    }
}
