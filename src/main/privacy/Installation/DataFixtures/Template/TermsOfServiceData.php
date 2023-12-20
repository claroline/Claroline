<?php

namespace Claroline\PrivacyBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class TermsOfServiceData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'terms_of_service';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Terms of Service',
                    'content' => $this->twig->render('@ClarolinePrivacy/template/terms_of_service.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Conditions d\'utilisation',
                    'content' => $this->twig->render('@ClarolinePrivacy/template/terms_of_service.fr.html.twig'),
                ],
            ],
        ];
    }
}
