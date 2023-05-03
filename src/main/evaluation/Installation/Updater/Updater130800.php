<?php

namespace Claroline\EvaluationBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130800 extends Updater
{
    private ObjectManager $om;

    public function __construct(
        ObjectManager $om
    ) {
        $this->om = $om;
    }

    public function postUpdate(): void
    {
        $this->updateTemplateContent('workspace_participation_certificate');
        $this->updateTemplateContent('workspace_success_certificate');
    }

    private function updateTemplateContent(string $typeName): void
    {
        $templateType = $this->om->getRepository(TemplateType::class)->findOneBy(['name' => $typeName]);
        if (!$templateType) {
            return;
        }

        /** @var Template[] $templates */
        $templates = $this->om->getRepository(Template::class)->findBy([
            'type' => $templateType,
        ]);

        if (!empty($templates)) {
            foreach ($templates as $template) {
                foreach ($template->getTemplateContents() as $templateContent) {
                    $templateContent->setTitle(str_replace('%evaluation_date%', '%evaluation_datetime%', $templateContent->getTitle()));
                    $templateContent->setContent(str_replace('%evaluation_date%', '%evaluation_datetime%', $templateContent->getContent()));

                    $this->om->persist($templateContent);
                }
            }

            $this->om->flush();
        }
    }
}
