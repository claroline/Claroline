<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro__open_badge_badge_class")
 */
class BadgeClass
{
    use UuidTrait;

    const ISSUING_MODE_ORGANIZATION = 'organization';
    const ISSUING_MODE_USER = 'user';
    const ISSUING_MODE_GROUP = 'group';
    const ISSUING_MODE_PEER = 'peer';
    const ISSUING_MODE_WORKSPACE = 'workspace';
    const ISSUING_MODE_AUTO = 'auto';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column()
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $description;

    /**
     * @ORM\Column
     *
     * @var string
     */
    private $image;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $criteria;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization")
     *
     * @var Organization
     */
    private $issuer;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     *
     * @var Organization
     */
    private $workspace;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @var int
     */
    private $enabled = true;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $durationValidation = null;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\OpenBadgeBundle\Entity\Assertion", mappedBy="badge")
     */
    private $assertions;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User")
     *
     * @var Organization
     */
    private $allowedIssuers;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Group")
     *
     * @var Organization
     */
    private $allowedIssuersGroups;

    /**
     * @ORM\Column(type="json_array")
     *
     * @var string
     */
    private $issuingMode = [self::ISSUING_MODE_ORGANIZATION];

    public function __construct()
    {
        $this->refreshUuid();
        $this->allowedIssuers = new ArrayCollection();
        $this->allowedIssuersGroups = new ArrayCollection();
    }

    /**
     * Get the value of Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of Id.
     *
     * @param int id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of Name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of Name.
     *
     * @param string name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of Description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of Description.
     *
     * @param string description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of Image.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of Image.
     *
     * @param string
     *
     * @return self
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get the value of Criteria.
     *
     * @return string
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Set the value of Criteria.
     *
     * @param string criteria
     *
     * @return self
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * Get the value of Issuer.
     *
     * @return Organization
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * Set the value of Issuer.
     *
     * @param Organization issuer
     *
     * @return self
     */
    public function setIssuer(Organization $issuer)
    {
        $this->issuer = $issuer;

        return $this;
    }

    public function setDurationValidation($duration)
    {
        $this->durationValidation = $duration;
    }

    public function getDurationValidation()
    {
        if (!$this->durationValidation) {
            //100 years validation !
            return 365 * 100;
        }

        return $this->durationValidation;
    }

    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setEnabled($bool)
    {
        $this->enabled = $bool;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function getUpdated()
    {
        return $this->updated;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setAllowedIssuers(array $users)
    {
        $this->allowedIssuers->clear();
        $this->allowedIssuers = $users;
    }

    public function setAllowedIssuersGroups(array $groups)
    {
        $this->allowedIssuersGroups->clear();
        $this->allowedIssuersGroups = $groups;
    }

    public function getAllowedIssuers()
    {
        return $this->allowedIssuers;
    }

    public function getAllowedIssuersGroups()
    {
        return $this->allowedIssuersGroups;
    }

    public function setIssuingMode(array $issuingMode)
    {
        $this->issuingMode = $issuingMode;
    }

    public function getIssuingMode()
    {
        return $this->issuingMode;
    }
}
