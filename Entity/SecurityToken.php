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
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\SecurityTokenRepository")
 * @ORM\Table(name="claro_security_token")
 * @DoctrineAssert\UniqueEntity("tokenName")
 */
class SecurityToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="token_name", unique=true)
     * @Assert\NotBlank()
     */
    protected $tokenName;
    
    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $token;

    /**
     * @ORM\Column(name="ip_address")
     * @Assert\NotBlank()
     * @Assert\Regex(
     *      pattern="/^[0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}([:][0-9]+)?$/",
     *      message="invalid_ip_address_format"
     * )
     */
    protected $ipAddress;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTokenName()
    {
        return $this->tokenName;
    }

    public function setTokenName($tokenName)
    {
        $this->tokenName = $tokenName;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }
}
