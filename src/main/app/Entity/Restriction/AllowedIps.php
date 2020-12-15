<?php

namespace Claroline\AppBundle\Entity\Restriction;

use Doctrine\ORM\Mapping as ORM;

trait AllowedIps
{
    /**
     * @var string[]
     *
     * @ORM\Column(name="allowed_ips", type="json_array", nullable=true)
     */
    private $allowedIps;

    /**
     * Returns the access code.
     *
     * @return string[]
     */
    public function getAllowedIps()
    {
        return $this->allowedIps;
    }

    /**
     * Sets the access code.
     *
     * @param array $allowedIps
     */
    public function setAllowedIps(array $allowedIps)
    {
        $this->allowedIps = $allowedIps;
    }
}
