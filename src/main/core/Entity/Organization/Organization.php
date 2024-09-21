<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Organization;

use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\IsPublic;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid as BaseUuid;

#[ORM\Table(name: 'claro__organization')]
#[ORM\Entity]
class Organization implements CrudEntityInterface
{
    use Code;
    use Id;
    use Uuid;
    use Name;
    use IsPublic;
    use Description;
    use Poster;
    use Thumbnail;

    #[ORM\Column(nullable: true, type: 'string')]
    private ?string $email = null;

    #[ORM\Column(name: 'is_default', type: 'boolean')]
    private bool $default = false;

    public function __construct()
    {
        $this->refreshUuid();
        // todo : generate unique from name for a more beautiful code
        $this->code = BaseUuid::uuid4()->toString();
    }

    public static function getIdentifiers(): array
    {
        return ['code'];
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and OrganizationController::HasUsersTrait.
     */
    public function addUser(User $user): void
    {
        $user->addOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and OrganizationController::HasUsersTrait.
     */
    public function removeUser(User $user): void
    {
        $user->removeOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and OrganizationController::addManagersAction.
     */
    public function addManager(User $user): void
    {
        $user->addAdministratedOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and OrganizationController::removeManagersAction.
     */
    public function removeManager(User $user): void
    {
        $user->removeAdministratedOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and OrganizationController::HasGroupsTrait.
     */
    public function addGroup(Group $group): void
    {
        $group->addOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and OrganizationController::HasGroupsTrait.
     */
    public function removeGroup(Group $group): void
    {
        $group->removeOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by OrganizationController::HasGroupsTrait.
     */
    public function addWorkspace(Workspace $workspace): void
    {
        $workspace->addOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by OrganizationController::HasWorkspacesTrait.
     */
    public function removeWorkspace(Workspace $workspace): void
    {
        $workspace->removeOrganization($this);
    }
}
