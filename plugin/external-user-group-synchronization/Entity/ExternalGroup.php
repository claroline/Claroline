<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 4/12/17
 */

namespace Claroline\ExternalSynchronizationBundle\Entity;

use Claroline\CoreBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ExternalGroup.
 *
 * @ORM\Table(name="claro_external_synchronized_group", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_group_by_source", columns={"external_group_id", "source_slug"})
 * })
 * @ORM\Entity(repositoryClass="Claroline\ExternalSynchronizationBundle\Repository\ExternalGroupRepository")
 */
class ExternalGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="external_group_id", type="string", nullable=false)
     */
    protected $externalGroupId;

    /**
     * @ORM\Column(name="source_slug", type="string", nullable=false)
     */
    protected $sourceSlug;

    /**
     * @var \Datetime
     *
     * @ORM\Column(type="date", name="last_synchronization_date")
     */
    protected $lastSynchronizationDate;

    /**
     * @ORM\Column(type="boolean", name="active", nullable=false)
     */
    protected $active;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Group")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     */
    protected $group;

    public function __construct($externalGroupId = null, $sourceSlug = null, Group $group = null)
    {
        $this->externalGroupId = $externalGroupId;
        $this->sourceSlug = $sourceSlug;
        $this->group = $group;
        $this->active = true;
        $this->lastSynchronizationDate = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getExternalGroupId()
    {
        return $this->externalGroupId;
    }

    /**
     * @param mixed $externalGroupId
     *
     * @return $this
     */
    public function setExternalGroupId($externalGroupId)
    {
        $this->externalGroupId = $externalGroupId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSourceSlug()
    {
        return $this->sourceSlug;
    }

    /**
     * @param mixed $sourceSlug
     *
     * @return $this
     */
    public function setSourceSlug($sourceSlug)
    {
        $this->sourceSlug = $sourceSlug;

        return $this;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Group $group
     *
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getLastSynchronizationDate()
    {
        return $this->lastSynchronizationDate;
    }

    /**
     * @param \Datetime $lastSynchronizationDate
     *
     * @return $this
     */
    public function setLastSynchronizationDate($lastSynchronizationDate)
    {
        $this->lastSynchronizationDate = $lastSynchronizationDate;

        return $this;
    }

    public function updateLastSynchronizationDate()
    {
        $now = new \DateTime();
        $lastSyncDate = $this->lastSynchronizationDate;
        if ($now->format('Y-m-d') !== $lastSyncDate->format('Y-m-d')) {
            $this->lastSynchronizationDate = new \DateTime();
            $this->active = true;

            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }
}
