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

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Organization\OrganizationRepository")
 * @ORM\Table(name="user_organization")
 */
class UserOrganizationReference
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     inversedBy="userOrganizationReferences"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization")
     * @ORM\JoinColumn(name="oganization_id", nullable=false, onDelete="CASCADE")
     */
    private $organization;

    /**
     * @ORM\Column(name="is_main", type="boolean")
     */
    private $isMain = false;

    public function isMain()
    {
        return $this->isMain;
    }

    public function setIsMain($boolean)
    {
        return $this->isMain = $boolean;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getOrganization()
    {
        return $this->organization;
    }
}
