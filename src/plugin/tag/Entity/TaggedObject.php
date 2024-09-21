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

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_tagbundle_tagged_object')]
#[ORM\UniqueConstraint(name: 'unique', columns: ['object_id', 'object_class', 'tag_id'])]
#[ORM\Entity(repositoryClass: \Claroline\TagBundle\Repository\TaggedObjectRepository::class)]
class TaggedObject
{
    use Id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'object_id', type: 'string')]
    private $objectId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'object_class')]
    private $objectClass;

    /**
     * @var string
     */
    #[ORM\Column(name: 'object_name', nullable: true)]
    private $objectName;

    /**
     *
     * @var Tag
     */
    #[ORM\JoinColumn(name: 'tag_id', onDelete: 'CASCADE', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Claroline\TagBundle\Entity\Tag::class, inversedBy: 'taggedObjects')]
    private $tag;

    /**
     * Get tag.
     *
     * @return Tag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set tag.
     */
    public function setTag(Tag $tag)
    {
        $this->tag = $tag;
    }

    /**
     * Get object id.
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set object id.
     *
     * @param string $objectId
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
    }

    /**
     * Get object class.
     *
     * @return string
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * Set object class.
     *
     * @param string $objectClass
     */
    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;
    }

    /**
     * Get object name.
     *
     * @return string
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * Set object name.
     *
     * @param string $objectName
     */
    public function setObjectName($objectName)
    {
        $this->objectName = $objectName;
    }
}
