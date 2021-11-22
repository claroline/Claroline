<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
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
        $this->log('Renaming "templates_management" tool into "templates"`...');

        $tool = $this->om->getRepository(Tool::class)->findOneBy(['name' => 'templates_management']);
        if (!empty($tool)) {
            $tool->setName('templates');
            $this->om->persist($tool);
            $this->om->flush();
        }
    }
}
