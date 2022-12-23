<?php

namespace Claroline\LogBundle\Entity\Archive;

use Claroline\LogBundle\Entity\AbstractLog;
use Claroline\LogBundle\Entity\SecurityLog;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(null)
 */
class SecurityLogArchive extends AbstractLog
{
    public const ARCHIVE_TABLE_PREFIX = 'claro_log_security_archive_';

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $doerId;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $doerUuid;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $doerUsername;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $doerIp;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $doerCountry;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $doerCity;

    public static function fromSecurityLog(SecurityLog $securityLog): self
    {
        $doer = $securityLog->getDoer();

        $archive = new self();
        $archive->setEvent($securityLog->getEvent());
        $archive->setDetails($securityLog->getDetails());
        $archive->setDate($securityLog->getDate());

        if ($doer) {
            $archive->setDoerId($doer->getId());
            $archive->setDoerUuid($doer->getUuid());
            $archive->setDoerUsername($doer->getUsername());
        }

        $archive->setDoerIp($securityLog->getDoerIp());
        $archive->setDoerCountry($securityLog->getCountry());
        $archive->setDoerCity($securityLog->getCity());

        return $archive;
    }

    public function getDoerId(): ?int
    {
        return $this->doerId;
    }

    public function setDoerId(?int $doerId): void
    {
        $this->doerId = $doerId;
    }

    public function getDoerUuid(): ?string
    {
        return $this->doerUuid;
    }

    public function setDoerUuid(?string $doerUuid): void
    {
        $this->doerUuid = $doerUuid;
    }

    public function getDoerUsername(): string
    {
        return $this->doerUsername;
    }

    public function setDoerUsername(?string $doerUsername): void
    {
        $this->doerUsername = $doerUsername;
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
