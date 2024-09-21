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

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractLog
{
    use Id;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected DateTimeInterface $date;

    #[ORM\Column(type: Types::STRING)]
    protected string $event;

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $details = null;

    
    #[ORM\JoinColumn(name: 'doer_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected ?User $doer = null;

    #[ORM\Column(name: 'doer_ip', type: Types::STRING, nullable: true)]
    protected ?string $doerIp = null;

    #[ORM\Column(name: 'doer_country', type: Types::STRING, nullable: true)]
    protected ?string $doerCountry = null;

    #[ORM\Column(name: 'doer_city', type: Types::STRING, nullable: true)]
    protected ?string $doerCity = null;

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $dateTime): void
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
