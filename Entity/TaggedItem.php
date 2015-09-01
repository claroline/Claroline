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
 * @ORM\Entity(repositoryClass="Claroline\TagBundle\Repository\TaggedItemRepository")
 * @ORM\Table(name="claro_tagbundle_tagged_item")
 * @DoctrineAssert\UniqueEntity({"itemId", "item_class", "tag"})
 */
class TaggedItem
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="item_id", type="integer")
     */
    protected $itemId;

    /**
     * @ORM\Column(name="item_class")
     */
    protected $itemClass;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\TagBundle\Entity\Tag"
     * )
     * @ORM\JoinColumn(name="tag_id", onDelete="CASCADE", nullable=false)
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

    public function getItemId()
    {
        return $this->itemId;
    }

    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    public function getItemClass()
    {
        return $this->itemClass;
    }

    public function setItemClass($itemClass)
    {
        $this->itemClass = $itemClass;
    }
}
