<?php

namespace Claroline\CursusBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\InstallationBundle\Updater\Updater;
use Psr\Log\LoggerInterface;

class Updater130001 extends Updater
{
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
        $this->renameTool('cursus', 'trainings');
        $this->renameTool('claroline_session_events_tool', 'training_events');

        $this->cleanTemplates();
    }

    private function renameTool($oldName, $newName)
    {
        $this->log(sprintf('Renaming `%s` tool into `%s`...', $oldName, $newName));

        $tool = $this->om->getRepository(Tool::class)->findOneBy(['name' => $oldName]);
        if (!empty($tool)) {
            $tool->setName($newName);
            $this->om->persist($tool);
            $this->om->flush();
        }
    }

    private function cleanTemplates()
    {
        $templateTypes = [
            'session_certificate',
            'session_event_certificate',
            'session_certificate_mail',
            'session_event_certificate_mail',
            'admin_certificate_mail',
            'session_invitation',
            'session_event_invitation',
        ];

        foreach ($templateTypes as $templateType) {
            $type = $this->om->getRepository(TemplateType::class)->findOneBy(['name' => $templateType]);

            if (!empty($type)) {
                $this->om->remove($type);
            }
        }

        $this->om->flush();
    }
}
