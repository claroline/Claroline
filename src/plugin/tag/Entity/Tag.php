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

use Claroline\AppBundle\Entity\Display\Color;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_tagbundle_tag')]
#[ORM\Entity]
class Tag
{
    use Id;
    use Uuid;
    // meta
    use Description;
    use Color;

    /**
     * The name of the tag.
     */
    #[ORM\Column(name: 'tag_name', unique: true)]
    private ?string $name;

    /**
     * The list of objects with the tag.
     */
    #[ORM\OneToMany(targetEntity: \Claroline\TagBundle\Entity\TaggedObject::class, mappedBy: 'tag')]
    private Collection $taggedObjects;

    public function __construct()
    {
        $this->refreshUuid();

        $this->taggedObjects = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Gets the list of objects with the tag.
     */
    public function getTaggedObjects(): Collection
    {
        return $this->taggedObjects;
    }

    public function addTaggedObject(TaggedObject $taggedObject): void
    {
        if (!$this->taggedObjects->contains($taggedObject)) {
            $this->taggedObjects->add($taggedObject);
        }
    }

    public function removeTaggedObject(TaggedObject $taggedObject): void
    {
        if ($this->taggedObjects->contains($taggedObject)) {
            $this->taggedObjects->removeElement($taggedObject);
        }
    }
}
