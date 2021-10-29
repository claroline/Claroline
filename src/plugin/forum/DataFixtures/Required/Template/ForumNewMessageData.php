<?php

namespace Claroline\ForumBundle\DataFixtures\Required\Template;

use Claroline\CoreBundle\DataFixtures\AbstractTemplateFixture;

class ForumNewMessageData extends AbstractTemplateFixture
{
    protected static function getTemplateType(): string
    {
        return 'forum_new_message';
    }

    protected function getSystemTemplates(): array
    {
        return [
            'Claroline Connect' => [
                'en' => [
                    'title' => '%subject%',
                    'content' => $this->twig->render('@ClarolineForum/template/forum_new_message.en.html.twig'),
                ],
                'fr' => [
                    'title' => '%subject%',
                    'content' => $this->twig->render('@ClarolineForum/template/forum_new_message.fr.html.twig'),
                ],
            ],
        ];
    }
}
