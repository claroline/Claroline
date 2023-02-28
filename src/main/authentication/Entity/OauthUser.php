<?php

namespace Claroline\AuthenticationBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class OauthUser.
 *
 * @ORM\Table(name="claro_oauth_user")
 * @ORM\Entity(repositoryClass="Claroline\AuthenticationBundle\Repository\OauthUserRepository")
 */
class OauthUser
{
    use Id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $oauthId;

    /**
     * @ORM\Column(type="string")
     */
    private $service;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    public function __construct($service = null, $oauthId = null, User $user = null)
    {
        $this->service = $service;
        $this->oauthId = $oauthId;
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param mixed $service
     *
     * @return $this
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOauthId()
    {
        return $this->oauthId;
    }

    /**
     * @param mixed $oauthId
     *
     * @return $this
     */
    public function setOauthId($oauthId)
    {
        $this->oauthId = $oauthId;

        return $this;
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
