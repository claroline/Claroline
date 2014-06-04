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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\CompetenceRepository")
 * @ORM\Table(name="claro_competence_users",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="competence_user_unique",
 *             columns={"competence_id", "user_id"}
 *         )
 *     }
 * )
 */
class UserCompetence {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Competence\Competence"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $competence;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $score;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $competence
     */
    public function setCompetence($competence)
    {
        $this->competence = $competence;
    }

    /**
     * @return mixed
     */
    public function getCompetence()
    {
        return $this->competence;
    }


    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }


} 