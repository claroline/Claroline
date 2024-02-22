<?php

namespace Claroline\PrivacyBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\PrivacyBundle\Entity\PrivacyParameters;

class PrivacyManager
{
    private ObjectManager $om;
    private TemplateManager $templateManager;

    public function __construct(ObjectManager $om, TemplateManager $templateManager)
    {
        $this->om = $om;
        $this->templateManager = $templateManager;
    }

    public function getParameters(): PrivacyParameters
    {
        $parameters = $this->om->getRepository(PrivacyParameters::class)->findOneBy([], ['id' => 'DESC']);

        if (empty($parameters)) {
            $parameters = new PrivacyParameters();
        }

        return $parameters;
    }

    public function updateParameters(PrivacyParameters $parameters): void
    {
        $this->om->persist($parameters);
        $this->om->flush();
    }

    public function getTosEnabled(string $locale): bool
    {
        return $this->getParameters()->getTosEnabled() && strlen(trim($this->getTosTemplate($locale))) > 0;
    }

    public function getTosTemplate(string $locale): string
    {
        $privacyParameters = $this->getParameters();

        $placeholders = [
            'country_storage' => $privacyParameters->getCountryStorage(),
            'dpo_name' => $privacyParameters->getDpoName(),
            'dpo_email' => $privacyParameters->getDpoEmail(),
            'dpo_phone' => $privacyParameters->getDpoPhone(),
            'dpo_address_street1' => $privacyParameters->getDpoAddressStreet1(),
            'dpo_address_street2' => $privacyParameters->getDpoAddressStreet2(),
            'dpo_address_postal_code' => $privacyParameters->getDpoAddressPostalCode(),
            'dpo_address_city' => $privacyParameters->getDpoAddressCity(),
            'dpo_address_state' => $privacyParameters->getDpoAddressState(),
            'dpo_address_country' => $privacyParameters->getDpoAddressCountry(),
        ];

        if ($privacyParameters->getTosTemplate()) {
            $content = $this->templateManager->getTemplateContent($privacyParameters->getTosTemplate(), $placeholders, $locale);
        } else {
            $content = $this->templateManager->getTemplate('terms_of_service', $placeholders, $locale);
        }

        return $content;
    }
}
