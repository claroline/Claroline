<?php

namespace Claroline\PrivacyBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\PrivacyBundle\Entity\Privacy;

class PrivacySerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return Privacy::class;
    }

    public function serialize(Privacy $privacy): array
    {
       return ([
            'id' => $privacy->getUuid(),
            'autoId' => $privacy->getId(),
            'dpo' =>[
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
            'isTermsOfServiceEnabled' => $privacy->getIsTermsOfServiceEnabled(),
        ]);
    }

    public function deserialize(array $data, Privacy $privacy): Privacy
    {
        $this->sipe('id', 'setUuid', $data, $privacy);
        $this->sipe('autoId', 'setId', $data, $privacy);
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
        $this->sipe('isTermsOfServiceEnabled', 'setIsTermsOfServiceEnabled', $data, $privacy);

        return $privacy;
    }
}