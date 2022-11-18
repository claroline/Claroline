<?php

namespace Claroline\AppBundle\Entity\Restriction;

use Doctrine\ORM\Mapping as ORM;

trait AccessCode
{
    /**
     * @ORM\Column(name="access_code", type="string", nullable=true)
     *
     * @var string
     */
    protected $accessCode;

    /**
     * Returns the access code.
     */
    public function getAccessCode(): ?string
    {
        return $this->accessCode;
    }

    /**
     * Sets the access code.
     */
    public function setAccessCode(?string $accessCode): void
    {
        $this->accessCode = $accessCode;
    }
}
