<?php

namespace Claroline\PrivacyBundle\Installation\Updater;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Updater\Updater;
use Claroline\PrivacyBundle\Entity\PrivacyParameters;

class Updater140000 extends Updater
{
    private PlatformConfigurationHandler $config;
    private ObjectManager $objectManager
    ;
    public function __construct(
        PlatformConfigurationHandler $config,
        ObjectManager $objectManager
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    public function postUpdate()
    {
        $privacyParameters = $this->objectManager->getRepository(PrivacyParameters::class)->findOneBy([], ['id' => 'DESC']);
        if (empty($privacyParameters)) {
            $privacyParameters = new PrivacyParameters();
            $privacyParameters->setDpoName($this->config->getParameter('privacy.dpo.name'));
            $privacyParameters->setDpoEmail($this->config->getParameter('privacy.dpo.email'));
            $privacyParameters->setDpoPhone($this->config->getParameter('privacy.dpo.phone'));
            $privacyParameters->setDpoAddress($this->config->getParameter('privacy.dpo.address'));
            $this->objectManager->persist($privacyParameters);
            $this->objectManager->flush();
        }

    }
}
