<?php

namespace Claroline\OpenBadgeBundle\Installation\DataFixtures\Template;

use Claroline\CoreBundle\Installation\DataFixtures\AbstractTemplateFixture;

class BadgeGrantedData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'badge_granted';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => 'Awarding of the badge %badge_name%',
                    'content' => $this->twig->render('@ClarolineOpenBadge/template/badge_granted.en.html.twig'),
                ],
                'fr' => [
                    'title' => 'Attribution du badge %badge_name%',
                    'content' => $this->twig->render('@ClarolineOpenBadge/template/badge_granted.fr.html.twig'),
                ],
            ],
        ];
    }
}
