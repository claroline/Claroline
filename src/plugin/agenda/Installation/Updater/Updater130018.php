<?php

namespace Claroline\AgendaBundle\Installation\Updater;

use Claroline\AgendaBundle\DataFixtures\PostInstall\LoadTemplateData;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Updater\Updater;
use Psr\Log\LoggerInterface;

class Updater130018 extends Updater
{
    private $om;
    private $dataFixtures;

    public function __construct(
        ObjectManager $om,
        LoadTemplateData $dataFixtures,
        LoggerInterface $logger = null
    ) {
        $this->om = $om;
        $this->dataFixtures = $dataFixtures;
        $this->logger = $logger;
    }

    public function postUpdate()
    {
        $this->dataFixtures->load($this->om);
    }
}
