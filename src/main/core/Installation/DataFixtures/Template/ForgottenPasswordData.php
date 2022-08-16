<?php

namespace Claroline\CoreBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

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
                    'content' => $this->twig->render('@ClarolineCore/template/forgotten_password.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Réinitialisation de votre mot de passe',
                    'content' => $this->twig->render('@ClarolineCore/template/forgotten_password.fr.html.twig'),
                ],
            ],
        ];
    }
}
