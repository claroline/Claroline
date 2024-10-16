<?php

namespace Claroline\AuthenticationBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Restriction\Locked;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Associates a User to an IP address to automatically log user for requests from this IP.
 * Used with the IpAuthenticator.
 *
 * @ORM\Table(name="claro_ip_user")
 *
 * @ORM\Entity
 */
class IpUser
{
    use Id;
    use Uuid;
    use Locked;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    private ?string $ip = null;

    /**
     * If true, the $ip field contains to ips separated by a , to define the range.
     *
     * @ORM\Column(name="is_range", type="boolean")
     */
    private bool $range = false;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     *
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?User $user = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    public function isRange(): bool
    {
        return $this->range;
    }

    public function setRange(bool $range): void
    {
        $this->range = $range;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function inRange(string $ip): bool
    {
        if ($this->range) {
            $range = explode(',', $this->ip);

            return ip2long($ip) <= ip2long($range[1]) && ip2long($range[0]) <= ip2long($ip);
        }

        return $ip === $this->ip;
    }
}
