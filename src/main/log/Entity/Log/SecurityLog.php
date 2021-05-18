<?php

namespace Claroline\LogBundle\Entity\Log;

use Claroline\CoreBundle\Entity\Log\AbstractLog;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_log_security")
 */
class SecurityLog extends AbstractLog
{
    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Claroline\LogBundle\Entity\User")
     * @ORM\JoinColumn(name="target_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $target;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Claroline\LogBundle\Entity\User")
     * @ORM\JoinColumn(name="doer_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $doer;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $country;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $doerIp;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $city;

    public function getTarget(): ?User
    {
        return $this->target;
    }

    public function setTarget(?User $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getDoerIp(): ?string
    {
        return $this->doerIp;
    }

    public function setDoerIp(?string $doerIp): self
    {
        $this->doerIp = $doerIp;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getDoer(): ?User
    {
        return $this->doer;
    }

    public function setDoer(?User $doer): self
    {
        $this->doer = $doer;

        return $this;
    }
}
