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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="user_organization",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="organization_unique_user", columns={"user_id", "organization_id"})
 *     }
 * )
 */
class UserOrganizationReference
{
    use Id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     inversedBy="userOrganizationReferences"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization",
     *     inversedBy="userOrganizationReferences",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="organization_id", nullable=false, onDelete="CASCADE")
     *
     * @var Organization
     */
    private $organization;

    /**
     * The organization is the main organization of the user.
     *
     * @ORM\Column(name="is_main", type="boolean")
     */
    private $main = false;

    /**
     * The user is a manager of the organization.
     *
     * @ORM\Column(name="is_manager", type="boolean")
     */
    private $manager = false;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function setOrganization(Organization $organization): void
    {
        $this->organization = $organization;
    }

    public function isMain(): bool
    {
        return $this->main;
    }

    public function setMain(bool $main): void
    {
        $this->main = $main;
    }

    public function isManager(): bool
    {
        return $this->manager;
    }

    public function setManager(bool $manager): void
    {
        $this->manager = $manager;
    }
}
