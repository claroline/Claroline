<?php

namespace Claroline\CoreBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

class UserEmailValidationData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'user_email_validation';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Email validation for %platform_name%',
                    'content' => $this->twig->render('@ClarolineCore/template/user_email_validation.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Validation du courriel pour %platform_name%',
                    'content' => $this->twig->render('@ClarolineCore/template/user_email_validation.fr.html.twig'),
                ],
            ],
        ];
    }
}
