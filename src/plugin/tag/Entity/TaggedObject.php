<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Entity;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\TagBundle\Finder\TaggedObjectType;
use Claroline\TagBundle\Repository\TaggedObjectRepository;
use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_tagbundle_tagged_object')]
#[ORM\UniqueConstraint(name: 'unique', columns: ['object_id', 'object_class', 'tag_id'])]
#[ORM\Entity(repositoryClass: TaggedObjectRepository::class)]
#[CrudEntity(finderClass: TaggedObjectType::class)]
class TaggedObject
{
    use Id;

    #[ORM\Column(name: 'object_id', type: Types::STRING)]
    private ?string $objectId = null;

    #[ORM\Column(name: 'object_class')]
    private ?string $objectClass = null;

    #[ORM\Column(name: 'object_name', nullable: true)]
    private ?string $objectName = null;

    #[ORM\JoinColumn(name: 'tag_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Tag::class, inversedBy: 'taggedObjects')]
    private ?Tag $tag = null;

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(Tag $tag): void
    {
        $this->tag = $tag;
    }

    public function getObjectId(): ?string
    {
        return $this->objectId;
    }

    public function setObjectId(string $objectId): void
    {
        $this->objectId = $objectId;
    }

    public function getObjectClass(): ?string
    {
        return $this->objectClass;
    }

    public function setObjectClass($objectClass): void
    {
        $this->objectClass = $objectClass;
    }

    public function getObjectName(): ?string
    {
        return $this->objectName;
    }

    public function setObjectName($objectName): void
    {
        $this->objectName = $objectName;
    }
}
