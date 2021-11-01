<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\InstallationBundle\Updater\Updater;
use Psr\Log\LoggerInterface;

class Updater130100 extends Updater
{
    /** @var ObjectManager */
    private $om;

    public function __construct(
        ObjectManager $om,
        LoggerInterface $logger = null
    ) {
        $this->om = $om;
        $this->logger = $logger;
    }

    public function preUpdate()
    {
        $this->renameTemplateTool();

        $this->renameTemplateType('claro_mail_layout', 'email_layout');
        $this->renameTemplateType('claro_mail_registration', 'user_registration');
        $this->renameTemplateType('claro_mail_validation', 'user_email_validation');
    }

    private function renameTemplateTool()
    {
        $this->log('Renaming "templates_management" tool into "templates"`...');

        $tool = $this->om->getRepository(Tool::class)->findOneBy(['name' => 'templates_management']);
        if (!empty($tool)) {
            $tool->setName('templates');
            $this->om->persist($tool);
            $this->om->flush();
        }
    }

    private function renameTemplateType(string $oldName, string $newName)
    {
        $this->log(sprintf('Renaming "%s" template type into "%s"`...', $oldName, $newName));

        $type = $this->om->getRepository(TemplateType::class)->findOneBy(['name' => $oldName]);
        if (!empty($type)) {
            $type->setName($newName);
            $this->om->persist($type);
            $this->om->flush();
        }
    }
}
