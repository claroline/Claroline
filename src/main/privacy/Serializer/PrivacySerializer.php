<?php

namespace Claroline\PrivacyBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\PrivacyBundle\Entity\PrivacyParameters;

class PrivacySerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return PrivacyParameters::class;
    }

    public function serialize(PrivacyParameters $privacy): array
    {
        return [
            'dpo' => [
                'name' => $privacy->getDpoName(),
                'email' => $privacy->getDpoEmail(),
                'phone' => $privacy->getDpoPhone(),
                'address' => [
                    'street1' => $privacy->getAddressStreet1(),
                    'street2' => $privacy->getAddressStreet2(),
                    'postalCode' => $privacy->getAddressPostalCode(),
                    'city' => $privacy->getAddressCity(),
                    'state' => $privacy->getAddressState(),
                    'country' => $privacy->getAddressCountry(),
                ],
            ],
            'countryStorage' => $privacy->getCountryStorage(),
            'termsOfService' => $privacy->getTermsOfService(),
            'termsOfServiceEnabled' => $privacy->IsTermsOfServiceEnabled(),
            //'publicationDate' => DateNormalizer::normalize($privacy->getPublicationDate()),
        ];
    }

    public function deserialize(array $data, PrivacyParameters $privacy): PrivacyParameters
    {
        $this->sipe('dpo.name', 'setDpoName', $data, $privacy);
        $this->sipe('dpo.email', 'setDpoEmail', $data, $privacy);
        $this->sipe('dpo.phone', 'setDpoPhone', $data, $privacy);

        $this->sipe('dpo.address.street1', 'setAddressStreet1', $data, $privacy);
        $this->sipe('dpo.address.street2', 'setAddressStreet2', $data, $privacy);
        $this->sipe('dpo.address.postalCode', 'setAddressPostalCode', $data, $privacy);
        $this->sipe('dpo.address.city', 'setAddressCity', $data, $privacy);
        $this->sipe('dpo.address.state', 'setAddressState', $data, $privacy);
        $this->sipe('dpo.address.country', 'setAddressCountry', $data, $privacy);

        $this->sipe('countryStorage', 'setCountryStorage', $data, $privacy);

        $this->sipe('termsOfService', 'setTermsOfService', $data, $privacy);
        $this->sipe('termsOfServiceEnabled', 'setTermsOfServiceEnabled', $data, $privacy);
        //$privacy->setPublicationDate(DateNormalizer::denormalize($data['publicationDate']));

        return $privacy;
    }
}
