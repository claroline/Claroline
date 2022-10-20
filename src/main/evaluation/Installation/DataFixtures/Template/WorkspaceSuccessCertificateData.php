<?php

namespace Claroline\OpenBadgeBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class WorkspaceSuccessCertificateData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'workspace_success_certificate';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Certificate of achievement in "%workspace_name%"',
                    'content' => $this->twig->render('@ClarolineEvaluation/template/workspace_success_certificate.en.pdf.twig'),
                ],
                'fr' => [
                    'title' => 'Certificat de réussite à "%workspace_name%"',
                    'content' => $this->twig->render('@ClarolineEvaluation/template/workspace_success_certificate.fr.pdf.twig'),
                ],
            ],
        ];
    }
}
