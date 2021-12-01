<?php

namespace Claroline\CoreBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class WorkspaceRegistrationData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'workspace_registration';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Registration to "%workspace_name%" workspace',
                    'content' => $this->twig->render('@ClarolineCore/template/workspace_registration.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Inscription à l\'espace d\'activités "%workspace_name%"',
                    'content' => $this->twig->render('@ClarolineCore/template/workspace_registration.fr.html.twig'),
                ],
            ],
        ];
    }
}
