<?php

namespace Claroline\CoreBundle\Entity\Log;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\Log\LogSecurityRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=LogSecurityRepository::class)
 * @ORM\Table(name="claro_log_security")
 */
class LogSecurity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $date;

    /**
     * @ORM\Column(type="text")
     */
    private $details;

    /**
     * @ORM\Column(type="string")
     */
    private $event;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $target;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $doer;

    /**
     * @ORM\Column(type="string")
     */
    private $country;

    /**
     * @ORM\Column(type="string")
     */
    private $doerIp;

    /**
     * @ORM\Column(type="string")
     */
    private $city;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $dateTime): self
    {
        $this->date = $dateTime;

        return $this;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public function setDetails(string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function setEvent(string $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getTarget(): ?User
    {
        return $this->target;
    }

    public function setTarget(User $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getDoerIp(): string
    {
        return $this->doerIp;
    }

    public function setDoerIp(?string $doerIp): string
    {
        $this->doerIp = $doerIp;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getDoer(): ?User
    {
        return $this->doer;
    }

    public function setDoer(User $doer): self
    {
        $this->doer = $doer;

        return $this;
    }
}
