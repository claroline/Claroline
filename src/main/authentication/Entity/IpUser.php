<?php

namespace Claroline\AuthenticationBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Associates a User to an IP address to automatically log user for requests from this IP.
 * Used with the IpAuthenticator.
 *
 * @ORM\Table(name="claro_ip_user")
 * @ORM\Entity
 */
class IpUser
{
    use Id;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     *
     * @var string
     */
    private $ip;

    /**
     * If true, the $ip field contains to ips separated by a , to define the range.
     *
     * @ORM\Column(name="is_range", type="boolean")
     *
     * @var bool
     */
    private $range = false;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    /**
     * Disallow edition/deletion from application.
     * Useful to declare a third party app without worrying about a user deleting it.
     *
     * @ORM\Column(name="is_locked", type="boolean")
     *
     * @var bool
     */
    private $locked = false;

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip)
    {
        $this->ip = $ip;
    }

    public function isRange(): bool
    {
        return $this->range;
    }

    public function setRange(bool $range)
    {
        $this->range = $range;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked)
    {
        $this->locked = $locked;
    }

    public function inRange(string $ip)
    {
        if ($this->range) {
            $range = explode(',', $this->ip);

            return ip2long($ip) <= ip2long($range[1]) && ip2long($range[0]) <= ip2long($ip);
        }

        return $ip === $this->ip;
    }
}
