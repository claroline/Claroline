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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Color;
use Claroline\CoreBundle\Entity\Model\Template;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Represents an obtainable badge.
 *
 * @ORM\Entity
 * @ORM\Table(name="claro__open_badge_badge_class")
 */
class BadgeClass
{
    use Color;
    use Id;
    use Template;
    use Uuid;

    /**
     * @ORM\Column()
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
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
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $criteria;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\OpenBadgeBundle\Entity\Rules\Rule",
     *     mappedBy="badge",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     *
     * @var Rule[]|ArrayCollection
     */
    private $rules;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization")
     *
     * @var Organization
     */
    private $issuer;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     *
     * @var Workspace
     */
    private $workspace;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $durationValidation = null;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $hideRecipients = false;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     *
     * @var \DateTime
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     *
     * @var \DateTime
     */
    protected $updated;

    /**
     * Allows whose owns the badge to grant it to others.
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $issuingPeer = false;

    /**
     * Notifies users when they are granted the badge.
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $notifyGrant = false;

    public function __construct()
    {
        $this->refreshUuid();

        $this->rules = new ArrayCollection();
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
     * @param string $name
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
     * @param string $description
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
     * @param string $criteria
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

    public function setWorkspace(Workspace $workspace = null)
    {
        $this->workspace = $workspace;
    }

    /**
     * @return Workspace
     */
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

    public function setHideRecipients($bool)
    {
        $this->hideRecipients = $bool;
    }

    public function getHideRecipients()
    {
        return $this->hideRecipients;
    }

    public function getUpdated()
    {
        return $this->updated;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setIssuingPeer(bool $issuingPeer)
    {
        $this->issuingPeer = $issuingPeer;
    }

    public function hasIssuingPeer(): bool
    {
        return $this->issuingPeer;
    }

    public function setNotifyGrant(bool $notifyGrant)
    {
        $this->notifyGrant = $notifyGrant;
    }

    public function getNotifyGrant(): bool
    {
        return $this->notifyGrant;
    }

    /**
     * @return Rule[]|ArrayCollection
     */
    public function getRules()
    {
        return $this->rules;
    }

    public function addRule(Rule $rule)
    {
        if (!$this->rules->contains($rule)) {
            $this->rules->add($rule);
            $rule->setBadge($this);
        }
    }

    public function removeRule(Rule $rule)
    {
        if ($this->rules->contains($rule)) {
            $this->rules->removeElement($rule);
            $rule->setBadge(null);
        }
    }

    // this is for security checks
    public function getOrganizations()
    {
        if (!empty($this->issuer)) {
            return [$this->issuer];
        }

        return [];
    }
}
