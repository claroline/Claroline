<?php

namespace Claroline\PrivacyBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\PrivacyBundle\Entity\PrivacyParameters;
use Claroline\TemplateBundle\Entity\Template;
use Claroline\TemplateBundle\Serializer\TemplateSerializer;

class PrivacyParametersSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly TemplateSerializer $templateSerializer
    ) {
    }

    public function getClass(): string
    {
        return PrivacyParameters::class;
    }

    public function serialize(PrivacyParameters $privacyParameters, ?array $options = []): array
    {
        $serialized = [
            'countryStorage' => $privacyParameters->getCountryStorage(),
            'dpo' => [
                'name' => $privacyParameters->getDpoName(),
                'email' => $privacyParameters->getDpoEmail(),
                'address' => [
                    'street1' => $privacyParameters->getDpoAddressStreet1(),
                    'street2' => $privacyParameters->getDpoAddressStreet2(),
                    'postalCode' => $privacyParameters->getDpoAddressPostalCode(),
                    'city' => $privacyParameters->getDpoAddressCity(),
                    'state' => $privacyParameters->getDpoAddressState(),
                    'country' => $privacyParameters->getDpoAddressCountry(),
                ],
                'phone' => $privacyParameters->getDpoPhone(),
            ],
            'tos' => [
                'enabled' => $privacyParameters->getTosEnabled(),
            ],
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            if ($privacyParameters->getTosTemplate()) {
                $serialized['tos']['template'] = $this->templateSerializer->serialize($privacyParameters->getTosTemplate(), $options);
            }
            if ($privacyParameters->getPrivacyTemplate()) {
                $serialized['privacyTemplate'] = $this->templateSerializer->serialize($privacyParameters->getPrivacyTemplate(), $options);
            }
        }

        return $serialized;
    }

    public function deserialize(array $data, PrivacyParameters $privacyParameters): PrivacyParameters
    {
        $this->sipe('countryStorage', 'setCountryStorage', $data, $privacyParameters);
        $this->sipe('dpo.name', 'setDpoName', $data, $privacyParameters);
        $this->sipe('dpo.email', 'setDpoEmail', $data, $privacyParameters);
        $this->sipe('dpo.address.street1', 'setDpoAddressStreet1', $data, $privacyParameters);
        $this->sipe('dpo.address.street2', 'setDpoAddressStreet2', $data, $privacyParameters);
        $this->sipe('dpo.address.postalCode', 'setDpoAddressPostalCode', $data, $privacyParameters);
        $this->sipe('dpo.address.city', 'setDpoAddressCity', $data, $privacyParameters);
        $this->sipe('dpo.address.state', 'setDpoAddressState', $data, $privacyParameters);
        $this->sipe('dpo.address.country', 'setDpoAddressCountry', $data, $privacyParameters);
        $this->sipe('dpo.phone', 'setDpoPhone', $data, $privacyParameters);

        if (array_key_exists('tos', $data) && array_key_exists('template', $data['tos'])) {
            $this->sipe('tos.enabled', 'setTosEnabled', $data, $privacyParameters);

            $template = null;
            if (!empty($data['tos']['template']) && !empty($data['tos']['template']['id'])) {
                $template = $this->om->getRepository(Template::class)->findOneBy(['uuid' => $data['tos']['template']['id']]);
            }
            $privacyParameters->setTosTemplate($template);
        }

        if (array_key_exists('privacyTemplate', $data)) {
            $template = null;
            if (!empty($data['privacyTemplate']) && !empty($data['privacyTemplate']['id'])) {
                $template = $this->om->getRepository(Template::class)->findOneBy(['uuid' => $data['privacyTemplate']['id']]);
            }
            $privacyParameters->setPrivacyTemplate($template);
        }

        return $privacyParameters;
    }
}
