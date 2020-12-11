<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BookingBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BookingBundle\Entity\Material;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MaterialSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var PublicFileSerializer */
    private $fileSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        PublicFileSerializer $fileSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
    }

    public function getSchema()
    {
        return '#/plugin/booking/material.json';
    }

    public function serialize(Material $material, array $options = []): array
    {
        return [
            'id' => $material->getUuid(),
            'code' => $material->getCode(),
            'name' => $material->getName(),
            'description' => $material->getDescription(),
            'quantity' => $material->getQuantity(),
            'poster' => $this->serializePoster($material),
            'thumbnail' => $this->serializeThumbnail($material),
            'permissions' => [
                'open' => $this->authorization->isGranted('OPEN', $material),
                'edit' => $this->authorization->isGranted('EDIT', $material),
                'delete' => $this->authorization->isGranted('DELETE', $material),
                'book' => $this->authorization->isGranted('BOOK', $material),
            ],
        ];
    }

    public function deserialize(array $data, Material $material, array $options): Material
    {
        $this->sipe('id', 'setUuid', $data, $material);
        $this->sipe('code', 'setCode', $data, $material);
        $this->sipe('name', 'setName', $data, $material);
        $this->sipe('description', 'setDescription', $data, $material);
        $this->sipe('quantity', 'setQuantity', $data, $material);

        if (isset($data['poster'])) {
            $material->setPoster($data['poster']['url'] ?? null);
        }

        if (isset($data['thumbnail'])) {
            $material->setThumbnail($data['thumbnail']['url'] ?? null);
        }

        return $material;
    }

    private function serializePoster(Material $material)
    {
        if (!empty($material->getPoster())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $material->getPoster()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    private function serializeThumbnail(Material $material)
    {
        if (!empty($material->getThumbnail())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $material->getThumbnail()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }
}
