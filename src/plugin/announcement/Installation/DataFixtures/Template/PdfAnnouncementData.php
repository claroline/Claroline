<?php

namespace Claroline\AnnouncementBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class PdfAnnouncementData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'pdf_announcement';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => '%title%',
                    'content' => $this->twig->render('@ClarolineAnnouncement/template/pdf_announcement.en.pdf.twig'),
                ],
                'fr' => [
                    'title' => '%title%',
                    'content' => $this->twig->render('@ClarolineAnnouncement/template/pdf_announcement.fr.pdf.twig'),
                ],
            ],
        ];
    }
}
