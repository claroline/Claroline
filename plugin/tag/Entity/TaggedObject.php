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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\TagBundle\Repository\TaggedObjectRepository")
 * @ORM\Table(name="claro_tagbundle_tagged_object",  uniqueConstraints={@ORM\UniqueConstraint(name="unique", columns={"object_id", "object_class", "object_name"})}))
 * @DoctrineAssert\UniqueEntity({"objectId", "objectClass", "tag"})
 */
class TaggedObject
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="object_id", type="string")
     *
     * @var string
     */
    protected $objectId;

    /**
     * @ORM\Column(name="object_class")
     *
     * @var string
     */
    protected $objectClass;

    /**
     * @ORM\Column(name="object_name", nullable=true)
     *
     * @var string
     */
    protected $objectName;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\TagBundle\Entity\Tag"
     * )
     * @ORM\JoinColumn(name="tag_id", onDelete="CASCADE", nullable=false)
     *
     * @var Tag
     */
    protected $tag;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function setTag(Tag $tag)
    {
        $this->tag = $tag;
    }

    public function getObjectId()
    {
        return $this->objectId;
    }

    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
    }

    public function getObjectClass()
    {
        return $this->objectClass;
    }

    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;
    }

    public function getObjectName()
    {
        return $this->objectName;
    }

    public function setObjectName($objectName)
    {
        $this->objectName = $objectName;
    }
}
