<?php

namespace Claroline\PrivacyBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Template\TemplateSerializer;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\PrivacyBundle\Entity\PrivacyParameters;

class PrivacyParametersSerializer
{
    use SerializerTrait;

    private ObjectManager $om;
    private TemplateSerializer $templateSerializer;

    public function __construct(
        ObjectManager $om,
        TemplateSerializer $templateSerializer
    ) {
        $this->om = $om;
        $this->templateSerializer = $templateSerializer;
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
        $this->sipe('tos.enabled', 'setTosEnabled', $data, $privacyParameters);

        if (array_key_exists('tos', $data) && array_key_exists('template', $data['tos'])) {
            $template = null;
            if (!empty($data['tos']['template']) && !empty($data['tos']['template']['id'])) {
                $template = $this->om->getRepository(Template::class)->findOneBy(['uuid' => $data['tos']['template']['id']]);
            }
            $privacyParameters->setTosTemplate($template);
        }

        return $privacyParameters;
    }
}
