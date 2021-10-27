<?php

namespace Claroline\CoreBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

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
