<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Location\Location;
use Claroline\CoreBundle\Entity\Organization\Organization;

class OrganizationSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
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
        $serialized = [
            'id' => $organization->getUuid(),
            'name' => $organization->getName(),
            'code' => $organization->getCode(),
            'email' => $organization->getEmail(),
            'type' => $organization->getType(),
            'meta' => [
                'default' => $organization->getDefault(),
                'position' => $organization->getPosition(),
            ],
            'restrictions' => [
                'public' => $organization->isPublic(),
                'users' => $organization->getMaxUsers(),
            ],
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'parent' => !empty($organization->getParent()) ? [
                    'id' => $organization->getParent()->getUuid(),
                    'name' => $organization->getParent()->getName(),
                    'code' => $organization->getParent()->getCode(),
                    'meta' => [
                        'default' => $organization->getParent()->getDefault(),
                    ],
                ] : null,
                'locations' => array_map(function (Location $location) {
                    return [
                        'id' => $location->getId(),
                        'name' => $location->getName(),
                    ];
                }, $organization->getLocations()->toArray()),
            ]);
        }

        if (in_array(Options::IS_RECURSIVE, $options)) {
            $serialized['children'] = array_map(function (Organization $child) use ($options) {
                return $this->serialize($child, $options);
            }, $organization->getChildren()->toArray());
        }

        return $serialized;
    }

    public function deserialize($data, Organization $organization = null, array $options = []): Organization
    {
        $this->sipe('name', 'setName', $data, $organization);
        $this->sipe('code', 'setCode', $data, $organization);
        $this->sipe('email', 'setEmail', $data, $organization);
        $this->sipe('type', 'setType', $data, $organization);
        $this->sipe('vat', 'setVat', $data, $organization);
        $this->sipe('restrictions.users', 'setMaxUsers', $data, $organization);
        $this->sipe('restrictions.public', 'setPublic', $data, $organization);

        if (isset($data['parent'])) {
            if (empty($data['parent'])) {
                $organization->setParent(null);
            } else {
                $parent = $this->om->getRepository(Organization::class)->findOneBy([
                    'uuid' => $data['parent']['id'],
                ]);
                $organization->setParent($parent);
            }
        }

        return $organization;
    }
}
