<?php

namespace Claroline\PrivacyBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Updater\Updater;
use Claroline\PrivacyBundle\Entity\PrivacyParameters;

class Updater140000 extends Updater
{
    private ObjectManager $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function postUpdate()
    {
        $file = 'C:\xampp\htdocs\Claroline\files\config\platform_options.json';

        if (file_exists($file)) {
            $jsonContent = file_get_contents($file);
            $platformOptions = json_decode($jsonContent, true);

            if (!empty($platformOptions['privacy'])) {
                $privacyParameters = $this->objectManager->getRepository(PrivacyParameters::class)->findOneBy([], ['id' => 'DESC']);

                if (!$privacyParameters) {
                    $privacyParameters = new PrivacyParameters();
                    $privacyParameters->setCountryStorage($platformOptions['privacy']['countryStorage']);
                    $privacyParameters->setDpoName($platformOptions['privacy']['dpo']['name']);
                    $privacyParameters->setDpoEmail($platformOptions['privacy']['dpo']['email']);
                    $privacyParameters->setAddressStreet1($platformOptions['privacy']['dpo']['address']['street1']);
                    $privacyParameters->setAddressStreet2($platformOptions['privacy']['dpo']['address']['street2']);
                    $privacyParameters->setAddressPostalCode($platformOptions['privacy']['dpo']['address']['postalCode']);
                    $privacyParameters->setAddressCity($platformOptions['privacy']['dpo']['address']['city']);
                    $privacyParameters->setAddressState($platformOptions['privacy']['dpo']['address']['state']);
                    $privacyParameters->setAddressCountry($platformOptions['privacy']['dpo']['address']['country']);
                    $privacyParameters->setDpoPhone($platformOptions['privacy']['dpo']['phone']);
                    $privacyParameters->setTermsOfServiceEnabled(false);

                    $this->objectManager->persist($privacyParameters);
                    $this->objectManager->flush();
                }
            }
        }
    }
}
