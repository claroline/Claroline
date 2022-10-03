<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Serializer\Location;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Location\Location;
use Claroline\CoreBundle\Entity\Location\Material;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MaterialSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var LocationSerializer */
    private $locationSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        LocationSerializer $locationSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->locationSerializer = $locationSerializer;
    }

    public function getSchema()
    {
        return '#/main/core/location/material.json';
    }

    public function serialize(Material $material, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $material->getUuid(),
                'code' => $material->getCode(),
                'name' => $material->getName(),
                'thumbnail' => $material->getThumbnail(),
            ];
        }

        return [
            'autoId' => $material->getId(),
            'id' => $material->getUuid(),
            'code' => $material->getCode(),
            'name' => $material->getName(),
            'thumbnail' => $material->getThumbnail(),
            'poster' => $material->getPoster(),
            'description' => $material->getDescription(),
            'quantity' => $material->getQuantity(),
            'location' => $material->getLocation() ? $this->locationSerializer->serialize($material->getLocation(), [Options::SERIALIZE_MINIMAL]) : null,
            'permissions' => [
                'open' => $this->authorization->isGranted('OPEN', $material),
                'edit' => $this->authorization->isGranted('EDIT', $material),
                'delete' => $this->authorization->isGranted('DELETE', $material),
            ],
        ];
    }

    public function deserialize(array $data, Material $material, array $options): Material
    {
        $this->sipe('id', 'setUuid', $data, $material);
        $this->sipe('code', 'setCode', $data, $material);
        $this->sipe('name', 'setName', $data, $material);
        $this->sipe('poster', 'setPoster', $data, $material);
        $this->sipe('thumbnail', 'setThumbnail', $data, $material);
        $this->sipe('description', 'setDescription', $data, $material);
        $this->sipe('quantity', 'setQuantity', $data, $material);

        if (isset($data['location'])) {
            $location = null;
            if (isset($data['location']['id'])) {
                $location = $this->om->getRepository(Location::class)->findOneBy(['uuid' => $data['location']['id']]);
            }

            $material->setLocation($location);
        }

        return $material;
    }
}
