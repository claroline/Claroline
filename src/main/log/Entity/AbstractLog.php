<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LogBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractLog
{
    use Id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected \DateTimeInterface $date;

    /**
     * @ORM\Column(type="string")
     */
    protected string $event;

    /**
     * @ORM\Column(type="text")
     */
    protected ?string $details = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     *
     * @ORM\JoinColumn(name="doer_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected ?User $doer = null;

    /**
     * @ORM\Column(name="doer_ip", type="string", nullable=true)
     */
    protected ?string $doerIp = null;

    /**
     * @ORM\Column(name="doer_country", type="string", nullable=true)
     */
    protected ?string $doerCountry = null;

    /**
     * @ORM\Column(name="doer_city", type="string", nullable=true)
     */
    protected ?string $doerCity = null;

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $dateTime): void
    {
        $this->date = $dateTime;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): void
    {
        $this->details = $details;
    }

    public function getEvent(): ?string
    {
        return $this->event;
    }

    public function setEvent(string $event): void
    {
        $this->event = $event;
    }

    public function getDoer(): ?User
    {
        return $this->doer;
    }

    public function setDoer(?User $doer): void
    {
        $this->doer = $doer;
    }

    public function getDoerIp(): ?string
    {
        return $this->doerIp;
    }

    public function setDoerIp(?string $doerIp): void
    {
        $this->doerIp = $doerIp;
    }

    public function getDoerCountry(): ?string
    {
        return $this->doerCountry;
    }

    public function setDoerCountry(?string $doerCountry): void
    {
        $this->doerCountry = $doerCountry;
    }

    public function getDoerCity(): ?string
    {
        return $this->doerCity;
    }

    public function setDoerCity(?string $doerCity): void
    {
        $this->doerCity = $doerCity;
    }
}
