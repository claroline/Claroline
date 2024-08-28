<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Claroline\AppBundle\Entity\Address;
use Claroline\AppBundle\Entity\IdentifiableInterface;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro__location")
 */
class Location implements IdentifiableInterface
{
    use Id;
    use Uuid;
    use Description;
    use Thumbnail;
    use Poster;
    use Address;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    private ?string $name = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $phone = null;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization")
     *
     * @ORM\JoinTable(name="claro__location_organization")
     */
    private Collection $organizations;

    public function __construct()
    {
        $this->refreshUuid();

        $this->organizations = new ArrayCollection();
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setPhone(string $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getOrganizations(): Collection
    {
        return $this->organizations;
    }

    public function addOrganization(Organization $organization): void
    {
        if (!$this->organizations->contains($organization)) {
            $this->organizations->add($organization);
        }
    }

    public function removeOrganization(Organization $organization): void
    {
        if ($this->organizations->contains($organization)) {
            $this->organizations->removeElement($organization);
        }
    }
}
