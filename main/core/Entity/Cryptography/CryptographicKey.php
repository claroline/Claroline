<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Cryptography;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_cryptographic_key")
 */
class CryptographicKey
{
    use UuidTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization", inversedBy="keys")
     */
    private $organization;

    /**
     * @ORM\Column(type="text")
     */
    private $publicKeyParam;

    /**
     * We shouldn't store that. Users should do it themselves. Handle that properly later I guess.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $privateKeyParam;

    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get the value of Id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of Id.
     *
     * @param mixed id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of User.
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of User.
     *
     * @param mixed user
     *
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of Organization.
     *
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set the value of Organization.
     *
     * @param mixed organization
     *
     * @return self
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get the value of Public Key Param.
     *
     * @return mixed
     */
    public function getPublicKeyParam()
    {
        return $this->publicKeyParam;
    }

    /**
     * Set the value of Public Key Param.
     *
     * @param mixed publicKeyParam
     *
     * @return self
     */
    public function setPublicKeyParam($publicKeyParam)
    {
        $this->publicKeyParam = $publicKeyParam;

        return $this;
    }

    /**
     * Get the value of We shouldn't store that. Users should do it themselves. Handle that properly later I guess.
     *
     * @return mixed
     */
    public function getPrivateKeyParam()
    {
        return $this->privateKeyParam;
    }

    /**
     * Set the value of We shouldn't store that. Users should do it themselves. Handle that properly later I guess.
     *
     * @param mixed privateKeyParam
     *
     * @return self
     */
    public function setPrivateKeyParam($privateKeyParam)
    {
        $this->privateKeyParam = $privateKeyParam;

        return $this;
    }
}
