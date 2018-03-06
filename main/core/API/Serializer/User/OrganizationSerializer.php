<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.organization")
 * @DI\Tag("claroline.serializer")
 */
class OrganizationSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /**
     * OrganizationSerializer constructor.
     *
     * @DI\InjectParams({
     *      "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Serializes an Organization entity for the JSON api.
     *
     * @param Organization $organization - the organization to serialize
     * @param array        $options
     *
     * @return array - the serialized representation of the workspace
     */
    public function serialize(Organization $organization, array $options = [])
    {
        $data = [
            'id' => $organization->getUuid(),
            'name' => $organization->getName(),
            'code' => $organization->getCode(),
            'email' => $organization->getEmail(),
            'type' => $organization->getType(),
            'parent' => !empty($organization->getParent()) ? [
                'id' => $organization->getParent()->getUuid(),
                'name' => $organization->getParent()->getName(),
            ] : null,
            'meta' => [
                'default' => $organization->getDefault(),
                'position' => $organization->getPosition(),
            ],
            'managers' => array_map(function (User $administrator) {
                return [
                    'id' => $administrator->getId(),
                    'username' => $administrator->getUsername(),
                ];
            }, $organization->getAdministrators()->toArray()),
            'locations' => array_map(function (Location $location) {
                return [
                    'id' => $location->getId(),
                    'name' => $location->getName(),
                ];
            }, $organization->getLocations()->toArray()),
        ];

        if (in_array(Options::IS_RECURSIVE, $options)) {
            $data['children'] = array_map(function (Organization $child) use ($options) {
                return $this->serialize($child, $options);
            }, $organization->getChildren()->toArray());
        }

        return $data;
    }

    public function deserialize($data, Organization $organization = null, array $options = [])
    {
        $this->sipe('name', 'setName', $data, $organization);
        $this->sipe('code', 'setCode', $data, $organization);
        $this->sipe('email', 'setEmail', $data, $organization);
        $this->sipe('type', 'setType', $data, $organization);
        $this->sipe('vat', 'setVat', $data, $organization);

        if (isset($data['parent'])) {
            if (empty($data['parent'])) {
                $organization->setParent(null);
            } else {
                $parent = $this->om->getRepository($this->getClass())->findOneBy([
                    'uuid' => $data['parent']['id'],
                ]);
                $organization->setParent($parent);
            }
        }
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Organization\Organization';
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/organization.json';
    }

    /**
     * @return string
     */
    public function getSamples()
    {
        return '#/main/core/organization';
    }
}
