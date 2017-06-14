<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 5/17/17
 */

namespace Claroline\ExternalSynchronizationBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ExternalUser.
 *
 * @ORM\Table(name="claro_external_synchronized_user", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_user_by_source", columns={"external_user_id", "source_slug"})
 * })
 * @ORM\Entity(repositoryClass="Claroline\ExternalSynchronizationBundle\Repository\ExternalUserRepository")
 */
class ExternalUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="external_user_id", type="string", nullable=false)
     */
    protected $externalUserId;

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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     */
    protected $user;

    public function __construct($externalUserId = null, $sourceSlug = null, User $user = null)
    {
        $this->externalUserId = $externalUserId;
        $this->sourceSlug = $sourceSlug;
        $this->user = $user;
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
    public function getExternalUserId()
    {
        return $this->externalUserId;
    }

    /**
     * @param mixed $externalUserId
     *
     * @return $this
     */
    public function setExternalUserId($externalUserId)
    {
        $this->externalUserId = $externalUserId;

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

            return true;
        }

        return false;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
