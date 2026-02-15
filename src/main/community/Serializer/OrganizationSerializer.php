<?php

namespace Claroline\CommunityBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class OrganizationSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization
    ) {
    }

    public function getName(): string
    {
        return 'organization';
    }

    public function getClass(): string
    {
        return Organization::class;
    }

    public function getSchema(): string
    {
        return '#/main/core/organization.json';
    }

    public function getSamples(): string
    {
        return '#/main/core/organization';
    }

    /**
     * Serializes an Organization entity for the JSON api.
     *
     * @param Organization $organization - the organization to serialize
     *
     * @return array - the serialized representation of the workspace
     */
    public function serialize(Organization $organization, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $organization->getUuid(),
                'name' => $organization->getName(),
                'code' => $organization->getCode(),
                'thumbnail' => $organization->getThumbnail(),
                'email' => $organization->getEmail(),
                'meta' => [
                    'public' => $organization->isPublic(),
                    'description' => $organization->getDescription(),
                ],
            ];
        }

        $serialized = [
            'id' => $organization->getUuid(),
            'autoId' => $organization->getId(),
            'name' => $organization->getName(),
            'code' => $organization->getCode(),
            'thumbnail' => $organization->getThumbnail(),
            'poster' => $organization->getPoster(),
            'email' => $organization->getEmail(),
            'phone' => $organization->getPhone(),
            'address' => [
                'street1' => $organization->getAddressStreet1(),
                'street2' => $organization->getAddressStreet2(),
                'postalCode' => $organization->getAddressPostalCode(),
                'city' => $organization->getAddressCity(),
                'state' => $organization->getAddressState(),
                'country' => $organization->getAddressCountry(),
            ],
            'meta' => [
                'description' => $organization->getDescription(),
                'default' => $organization->isDefault(),
                'public' => $organization->isPublic(),
            ],
            'restrictions' => [ // deprecated
                'public' => $organization->isPublic(),
            ],
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $administrate = $this->authorization->isGranted('ADMINISTRATE', $organization);
            $serialized['permissions'] = [
                'open' => $administrate || $this->authorization->isGranted('OPEN', $organization),
                'edit' => $administrate || $this->authorization->isGranted('EDIT', $organization),
                'administrate' => $administrate,
                'delete' => $administrate,
            ];
        }

        return $serialized;
    }

    public function deserialize(array $data, ?Organization $organization = null, array $options = []): Organization
    {
        $this->sipe('name', 'setName', $data, $organization);
        $this->sipe('code', 'setCode', $data, $organization);
        $this->sipe('email', 'setEmail', $data, $organization);
        $this->sipe('poster', 'setPoster', $data, $organization);
        $this->sipe('thumbnail', 'setThumbnail', $data, $organization);
        $this->sipe('meta.description', 'setDescription', $data, $organization);
        $this->sipe('meta.public', 'setPublic', $data, $organization);
        $this->sipe('meta.default', 'setDefault', $data, $organization);
        $this->sipe('phone', 'setPhone', $data, $organization);

        if (isset($data['address'])) {
            $this->sipe('address.street1', 'setAddressStreet1', $data, $organization);
            $this->sipe('address.street2', 'setAddressStreet2', $data, $organization);
            $this->sipe('address.postalCode', 'setAddressPostalCode', $data, $organization);
            $this->sipe('address.city', 'setAddressCity', $data, $organization);
            $this->sipe('address.state', 'setAddressState', $data, $organization);
            $this->sipe('address.country', 'setAddressCountry', $data, $organization);
        }

        return $organization;
    }
}
