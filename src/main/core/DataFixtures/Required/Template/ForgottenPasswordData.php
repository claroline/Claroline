<?php

namespace Claroline\CoreBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

class ForgottenPasswordData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'forgotten_password';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Resetting your password',
                    'content' => $this->twig->render('@ClarolineCore/template/password_initialization.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Réinitialisation de votre mot de passe',
                    'content' => $this->twig->render('@ClarolineCore/template/password_initialization.fr.html.twig'),
                ],
            ],
        ];
    }
}
