<?php

namespace Claroline\PrivacyBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

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
                    'title' => 'Terms of use',
                    'content' => $this->twig->render('@ClarolinePrivacy/template/privacy.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Conditions d\'utilisation',
                    'content' => $this->twig->render('@ClarolinePrivacy/template/privacy.fr.html.twig'),
                ],
            ],
        ];
    }
}
