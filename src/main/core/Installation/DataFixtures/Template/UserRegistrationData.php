<?php

namespace Claroline\CoreBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class UserRegistrationData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'user_registration';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Registration to %platform_name%',
                    'content' => $this->twig->render('@ClarolineCore/template/user_registration.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Inscription à %platform_name%',
                    'content' => $this->twig->render('@ClarolineCore/template/user_registration.fr.html.twig'),
                ],
            ],
        ];
    }
}
