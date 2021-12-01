<?php

namespace Claroline\CoreBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class EmailLayoutData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'email_layout';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'New message from %platform_name%',
                    'content' => $this->twig->render('@ClarolineCore/template/email_layout.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Nouveau message de %platform_name%',
                    'content' => $this->twig->render('@ClarolineCore/template/email_layout.fr.html.twig'),
                ],
            ],
        ];
    }
}
