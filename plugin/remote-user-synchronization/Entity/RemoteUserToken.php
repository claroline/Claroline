<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\RemoteUserSynchronizationBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\RemoteUserSynchronizationBundle\Repository\RemoteUserTokenRepository")
 * @ORM\Table(name="claro_remote_user_token")
 * @DoctrineAssert\UniqueEntity("user")
 */
class RemoteUserToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column()
     */
    protected $token;

    /**
     * @ORM\Column(name="expiration_date", type="datetime", nullable=false)
     */
    protected $expirationDate;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $activated;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    public function isActivated()
    {
        return $this->activated;
    }

    public function setActivated($activated)
    {
        $this->activated = $activated;
    }
}
