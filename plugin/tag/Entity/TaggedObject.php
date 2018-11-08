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
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\TagBundle\Repository\TaggedObjectRepository")
 * @ORM\Table(name="claro_tagbundle_tagged_object", uniqueConstraints={@ORM\UniqueConstraint(name="unique", columns={"object_id", "object_class", "object_name"})}))
 * @DoctrineAssert\UniqueEntity({"objectId", "objectClass", "tag"})
 */
class TaggedObject
{
    use Id;

    /**
     * @ORM\Column(name="object_id", type="string")
     *
     * @var string
     */
    private $objectId;

    /**
     * @ORM\Column(name="object_class")
     *
     * @var string
     */
    private $objectClass;

    /**
     * @ORM\Column(name="object_name", nullable=true)
     *
     * @var string
     */
    private $objectName;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\TagBundle\Entity\Tag"
     * )
     * @ORM\JoinColumn(name="tag_id", onDelete="CASCADE", nullable=false)
     *
     * @var Tag
     */
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
     *
     * @param Tag $tag
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
