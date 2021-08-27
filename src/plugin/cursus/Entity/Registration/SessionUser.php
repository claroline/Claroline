<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity\Registration;

use Claroline\CursusBundle\Entity\Session;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\SessionUserRepository")
 * @ORM\Table(
 *     name="claro_cursusbundle_course_session_user",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="training_session_unique_user", columns={"session_id", "user_id"})
 *     }
 * )
 */
class SessionUser extends AbstractUserRegistration
{
    const STATUS_PENDING = 0;
    const STATUS_REFUSED = 1;
    const STATUS_VALIDATED = 2;
    const STATUS_MANAGED = 3;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\Session")
     * @ORM\JoinColumn(name="session_id", nullable=false, onDelete="CASCADE")
     *
     * @var Session
     */
    private $session;

    /**
     * The registration has to be managed by another service.
     *
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $remark = '';

    public function getSession(): Session
    {
        return $this->session;
    }

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    public function setRemark(string $remark)
    {
        $this->remark = $remark;
    }
}
