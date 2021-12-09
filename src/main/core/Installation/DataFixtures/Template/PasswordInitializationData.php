<?php

namespace Claroline\CoreBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class PasswordInitializationData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'password_initialization';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Password initialization',
                    'content' => $this->twig->render('@ClarolineCore/template/password_initialization.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Initialisation du mot de passe',
                    'content' => $this->twig->render('@ClarolineCore/template/password_initialization.fr.html.twig'),
                ],
            ],
        ];
    }
}
