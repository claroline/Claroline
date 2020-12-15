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
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_cursusbundle_course_session_group",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="training_session_unique_group", columns={"session_id", "group_id"})
 *     }
 * )
 */
class SessionGroup extends AbstractGroupRegistration
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\Session")
     * @ORM\JoinColumn(name="session_id", nullable=false, onDelete="CASCADE")
     *
     * @var Session
     */
    private $session;

    public function getSession(): Session
    {
        return $this->session;
    }

    public function setSession(Session $session)
    {
        $this->session = $session;
    }
}
