<?php

namespace Claroline\OpenBadgeBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class WorkspaceParticipationCertificateData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'workspace_participation_certificate';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Certificate of participation in "%workspace_name%"',
                    'content' => $this->twig->render('@ClarolineEvaluation/template/workspace_participation_certificate.en.pdf.twig'),
                ],
                'fr' => [
                    'title' => 'Certificat de participation à "%workspace_name%"',
                    'content' => $this->twig->render('@ClarolineEvaluation/template/workspace_participation_certificate.fr.pdf.twig'),
                ],
            ],
        ];
    }
}
