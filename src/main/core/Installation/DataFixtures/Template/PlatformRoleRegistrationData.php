<?php

namespace Claroline\CoreBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class PlatformRoleRegistrationData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'platform_role_registration';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Your permissions on the platform have changed',
                    'content' => $this->twig->render('@ClarolineCore/template/platform_role_registration.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Vos permissions sur la plateforme ont changées',
                    'content' => $this->twig->render('@ClarolineCore/template/platform_role_registration.fr.html.twig'),
                ],
            ],
        ];
    }
}
