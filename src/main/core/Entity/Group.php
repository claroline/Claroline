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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\AppBundle\Entity\Restriction\Locked;
use Claroline\CommunityBundle\Model\HasOrganizations;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CommunityBundle\Repository\GroupRepository")
 * @ORM\Table(name="claro_group")
 */
class Group extends AbstractRoleSubject
{
    use Id;
    use Uuid;
    use Description;
    use Poster;
    use Thumbnail;
    use Locked;
    use HasOrganizations;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"},
     *     mappedBy="groups"
     * )
     */
    private $users;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     cascade={"persist"},
     *     inversedBy="groups"
     * )
     * @ORM\JoinTable(name="claro_group_role")
     */
    protected $roles;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization",
     *     inversedBy="groups"
     * )
     *
     * @var Collection|Organization[]
     */
    private Collection $organizations;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Location\Location",
     *     inversedBy="groups"
     * )
     *
     * @deprecated should not be declared here. (also Groups are already linked to Organizations which are linked to Locations)
     */
    private $locations;

    public function __construct()
    {
        parent::__construct();

        $this->refreshUuid();

        $this->users = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addUser(User $user)
    {
        if (!$user->getGroups()->contains($this)) {
            $user->getGroups()->add($this);
        }
    }

    public function removeUser(User $user)
    {
        $user->getGroups()->removeElement($this);
    }

    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @deprecated
     */
    public function getUserIds(): array
    {
        $users = $this->getUsers();
        $userIds = [];
        foreach ($users as $user) {
            array_push($userIds, $user->getId());
        }

        return $userIds;
    }

    /**
     * @return ArrayCollection
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @deprecated use isLocked()
     */
    public function isReadOnly(): bool
    {
        return $this->isLocked();
    }

    /**
     * @deprecated use setLocked()
     */
    public function setReadOnly(bool $value)
    {
        $this->setLocked($value);
    }
}
