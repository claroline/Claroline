<?php

namespace Claroline\PrivacyBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;
use Claroline\PrivacyBundle\Entity\PrivacyParameters;

class Updater140000 extends Updater
{
    private PlatformConfigurationHandler $config;
    private ObjectManager $objectManager;

    public function __construct(
        PlatformConfigurationHandler $config,
        ObjectManager $objectManager
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    public function postUpdate()
    {
        $this->log('Update Privacy ...');

        $privacyParameters = $this->objectManager->getRepository(PrivacyParameters::class)->findOneBy(['id' => 'DESC']);
        if (empty($privacyParameters)) {
            $privacyParameters = new PrivacyParameters();
            $privacyParameters->setDpoName($this->config->getParameter('privacy.dpo.name'));
            $privacyParameters->setDpoEmail($this->config->getParameter('privacy.dpo.email'));
            $privacyParameters->setDpoPhone($this->config->getParameter('privacy.dpo.phone'));
            $privacyParameters->setAddressStreet1($this->config->getParameter('privacy.dpo.address.street1'));
            $privacyParameters->setAddressStreet2($this->config->getParameter('privacy.dpo.address.street2'));
            $privacyParameters->setAddressPostalCode($this->config->getParameter('privacy.dpo.address.postalCode'));
            $privacyParameters->setAddressCity($this->config->getParameter('privacy.dpo.address.city'));
            $privacyParameters->setAddressCountry($this->config->getParameter('privacy.dpo.address.country'));
            $privacyParameters->setAddressState($this->config->getParameter('privacy.dpo.address.state'));
            $privacyParameters->setCountryStorage($this->config->getParameter('privacy.countryStorage'));
            $this->objectManager->persist($privacyParameters);
            $this->objectManager->flush();
        }
    }
}
