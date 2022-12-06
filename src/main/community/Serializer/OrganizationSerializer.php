<?php

namespace Claroline\CommunityBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Location\Location;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class OrganizationSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om
    ) {
        $this->authorization = $authorization;
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
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $organization->getUuid(),
                'name' => $organization->getName(),
                'code' => $organization->getCode(),
                'thumbnail' => $organization->getThumbnail(),
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
            'type' => $organization->getType(),
            'meta' => [
                'description' => $organization->getDescription(),
                'default' => $organization->isDefault(),
                'position' => $organization->getPosition(),
            ],
            'restrictions' => [
                'public' => $organization->isPublic(),
                'users' => $organization->getMaxUsers(),
            ],
            'parent' => !empty($organization->getParent()) ? $this->serialize($organization->getParent(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
        ];

        if (!in_array(SerializerInterface::SERIALIZE_LIST, $options)) {
            $serialized['locations'] = array_map(function (Location $location) {
                return [
                    'id' => $location->getId(),
                    'name' => $location->getName(),
                ];
            }, $organization->getLocations()->toArray());
        }

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $serialized['permissions'] = [
                'open' => $this->authorization->isGranted('OPEN', $organization),
                'edit' => $this->authorization->isGranted('EDIT', $organization),
                'delete' => $this->authorization->isGranted('DELETE', $organization),
            ];
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
        $this->sipe('poster', 'setPoster', $data, $organization);
        $this->sipe('thumbnail', 'setThumbnail', $data, $organization);
        $this->sipe('meta.description', 'setDescription', $data, $organization);
        $this->sipe('restrictions.users', 'setMaxUsers', $data, $organization);
        $this->sipe('restrictions.public', 'setPublic', $data, $organization);

        if (array_key_exists('parent', $data)) {
            $parent = null;
            if (!empty($data['parent'])) {
                $parent = $this->om->getRepository(Organization::class)->findOneBy([
                    'uuid' => $data['parent']['id'],
                ]);
            }

            $organization->setParent($parent);
        }

        return $organization;
    }
}
