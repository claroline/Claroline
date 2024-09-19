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
                'meta' => [
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
            'meta' => [
                'description' => $organization->getDescription(),
                'default' => $organization->isDefault(),
            ],
            'restrictions' => [
                'public' => $organization->isPublic(),
            ],
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $edit = $this->authorization->isGranted('EDIT', $organization);
            $serialized['permissions'] = [
                'open' => $edit || $this->authorization->isGranted('OPEN', $organization),
                'edit' => $edit,
                'delete' => $edit || $this->authorization->isGranted('DELETE', $organization),
            ];
        }

        return $serialized;
    }

    public function deserialize(array $data, Organization $organization = null, array $options = []): Organization
    {
        $this->sipe('name', 'setName', $data, $organization);
        $this->sipe('code', 'setCode', $data, $organization);
        $this->sipe('email', 'setEmail', $data, $organization);
        $this->sipe('poster', 'setPoster', $data, $organization);
        $this->sipe('thumbnail', 'setThumbnail', $data, $organization);
        $this->sipe('meta.description', 'setDescription', $data, $organization);
        $this->sipe('restrictions.public', 'setPublic', $data, $organization);

        return $organization;
    }
}
