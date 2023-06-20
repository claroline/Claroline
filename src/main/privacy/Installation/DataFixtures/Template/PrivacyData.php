<?php

namespace Claroline\PrivacyBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PrivacyData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'privacy';
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
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