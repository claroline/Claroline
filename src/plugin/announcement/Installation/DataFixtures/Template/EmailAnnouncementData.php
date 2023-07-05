<?php

namespace Claroline\AnnouncementBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class EmailAnnouncementData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'email_announcement';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => '%title%',
                    'content' => $this->twig->render('@ClarolineAnnouncement/template/email_announcement.en.html.twig'),
                ],
                'fr' => [
                    'title' => '%title%',
                    'content' => $this->twig->render('@ClarolineAnnouncement/template/email_announcement.fr.html.twig'),
                ],
            ],
        ];
    }
}
