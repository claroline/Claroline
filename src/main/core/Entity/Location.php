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

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\Address;
use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\CommunityBundle\Model\HasOrganizations;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Finder\LocationType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'claro__location')]
#[CrudEntity(finderClass: LocationType::class)]
class Location implements CrudEntityInterface
{
    use Id;
    use Uuid;
    use Description;
    use Thumbnail;
    use Poster;
    use Address;
    use HasOrganizations;

    #[ORM\Column]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?string $phone = null;

    /**
     * @var Collection<int, Organization>
     */
    #[ORM\JoinTable(name: 'claro__location_organization')]
    #[ORM\ManyToMany(targetEntity: Organization::class)]
    private Collection $organizations;

    public function __construct()
    {
        $this->refreshUuid();

        $this->organizations = new ArrayCollection();
    }

    public static function getIdentifiers(): array
    {
        return [];
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
}
