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
use Claroline\CommunityBundle\Finder\GroupType;
use Claroline\CommunityBundle\Repository\GroupRepository;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\AppBundle\Entity\Restriction\Locked;
use Claroline\CommunityBundle\Model\HasOrganizations;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_group')]
#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[CrudEntity(
    finderClass: GroupType::class
)]
class Group extends AbstractRoleSubject implements CrudEntityInterface
{
    use Id;
    use Uuid;
    use Name;
    use Description;
    use Poster;
    use Thumbnail;
    use Locked;
    use HasOrganizations;
    use Code;

    
    /**
     * @var Collection<int, Role>
     */
    #[ORM\JoinTable(name: 'claro_group_role')]
    #[ORM\ManyToMany(targetEntity: Role::class, fetch: 'EXTRA_LAZY')]
    protected Collection $roles;

    /**
     * @var Collection<int, Organization>
     */
    #[ORM\ManyToMany(targetEntity: Organization::class, fetch: 'EXTRA_LAZY')]
    private Collection $organizations;

    public function __construct()
    {
        parent::__construct();

        $this->refreshUuid();

        $this->organizations = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    public static function getIdentifiers(): array
    {
        return ['code'];
    }

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and GroupController::HasUsersTrait.
     */
    public function addUser(User $user): void
    {
        if (!$user->getGroups()->contains($this)) {
            $user->getGroups()->add($this);
        }
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and GroupController::HasUsersTrait.
     */
    public function removeUser(User $user): void
    {
        $user->getGroups()->removeElement($this);
    }
}
