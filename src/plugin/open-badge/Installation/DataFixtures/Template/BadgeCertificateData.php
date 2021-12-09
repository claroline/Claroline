<?php

namespace Claroline\OpenBadgeBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class BadgeCertificateData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'badge_certificate';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Certificate %badge_name%',
                    'content' => $this->twig->render('@ClarolineOpenBadge/template/badge_certificate.en.pdf.twig'),
                ],
                'fr' => [
                    'title' => 'Certificat %badge_name%',
                    'content' => $this->twig->render('@ClarolineOpenBadge/template/badge_certificate.fr.pdf.twig'),
                ],
            ],
        ];
    }
}
