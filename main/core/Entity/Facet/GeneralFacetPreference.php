<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Facet;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\GeneralFacetPreferenceRepository")
 * @ORM\Table(name="claro_general_facet_preference")
 */
class GeneralFacetPreference
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_facet_admin"})
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_facet_admin"})
     */
    protected $baseData;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_facet_admin"})
     */
    protected $email;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_facet_admin"})
     */
    protected $phone;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_facet_admin"})
     */
    protected $sendMail;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"api_facet_admin"})
     */
    protected $sendMessage;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="generalFacetPreference"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"api_facet_admin"})
     */
    protected $role;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setBaseData($boolean)
    {
        $this->baseData = $boolean;
    }

    public function getBaseData()
    {
        return $this->baseData;
    }

    public function setEmail($boolean)
    {
        $this->email = $boolean;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setPhone($boolean)
    {
        $this->phone = $boolean;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setSendMail($boolean)
    {
        $this->sendMail = $boolean;
    }

    public function getSendMail()
    {
        return $this->sendMail;
    }

    public function setSendMessage($boolean)
    {
        $this->sendMessage = $boolean;
    }

    public function getSendMessage()
    {
        return $this->sendMessage;
    }

    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }
}
