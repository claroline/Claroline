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
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Color;
use Claroline\AppBundle\Entity\Meta\Description;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_tagbundle_tag")
 */
class Tag
{
    use Id;
    use Uuid;
    // meta
    use Description;
    use Color;

    /**
     * The name of the tag.
     *
     * @ORM\Column(name="tag_name", unique=true)
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $name;

    /**
     * The list of objects with the tag.
     *
     * @ORM\OneToMany(targetEntity="Claroline\TagBundle\Entity\TaggedObject", mappedBy="tag")
     *
     * @var ArrayCollection|TaggedObject[]
     */
    private $taggedObjects;

    public function __construct()
    {
        $this->refreshUuid();

        $this->taggedObjects = new ArrayCollection();
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Gets the list of objects with the tag.
     *
     * @return TaggedObject[]
     */
    public function getTaggedObjects()
    {
        return $this->taggedObjects;
    }

    public function addTaggedObject(TaggedObject $taggedObject)
    {
        if (!$this->taggedObjects->contains($taggedObject)) {
            $this->taggedObjects->add($taggedObject);
        }
    }

    public function removeTaggedObject(TaggedObject $taggedObject)
    {
        if ($this->taggedObjects->contains($taggedObject)) {
            $this->taggedObjects->removeElement($taggedObject);
        }
    }
}
