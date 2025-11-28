<?php

namespace Claroline\EvaluationBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Updater\Updater;
use Claroline\TemplateBundle\Entity\Template;
use Claroline\TemplateBundle\Entity\TemplateContent;

class Updater150010 extends Updater
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function postUpdate(): void
    {
        $template = $this->om->getRepository(Template::class)->findOneBy([
            'type' => 'evaluation_participation_certificate',
            'default' => true,
        ]);
        if ($template) {
            $this->duplicateTemplate('workspace_participation_certificate', $template);
        }

        $template = $this->om->getRepository(Template::class)->findOneBy([
            'type' => 'evaluation_success_certificate',
            'default' => true,
        ]);
        if ($template) {
            $this->duplicateTemplate('workspace_success_certificate', $template);
        }
    }

    private function duplicateTemplate(string $type, Template $template): void
    {
        $copy = new Template();
        $copy->setType($type);
        $copy->setName($template->getName());
        $copy->setDescription($template->getDescription());
        $copy->setDefault($template->isDefault());

        foreach ($copy->getTemplateContents() as $content) {
            $copyContent = new TemplateContent();
            $copyContent->setTitle($content->getTitle());
            $copyContent->setContent($content->getContent());
            $copyContent->setLang($content->getLang());
            $this->om->persist($copyContent);

            $copy->addTemplateContent($copyContent);
        }

        $this->om->persist($copy);
        $this->om->flush();
    }
}
