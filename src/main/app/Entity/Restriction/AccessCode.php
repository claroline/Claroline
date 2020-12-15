<?php

namespace Claroline\AppBundle\Entity\Restriction;

use Doctrine\ORM\Mapping as ORM;

trait AccessCode
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="access_code", type="string", nullable=true)
     */
    private $accessCode;

    /**
     * Returns the access code.
     *
     * @return \DateTime
     */
    public function getAccessCode()
    {
        return $this->accessCode;
    }

    /**
     * Sets the access code.
     *
     * @param string $accessCode
     */
    public function setAccessCode($accessCode)
    {
        $this->accessCode = $accessCode;
    }
}
