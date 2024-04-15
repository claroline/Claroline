<?php

namespace Claroline\AppBundle\Entity\Restriction;

use Doctrine\ORM\Mapping as ORM;

/**
 * @deprecated the feature has been removed.
 */
trait AllowedIps
{
    /**
     * @ORM\Column(name="allowed_ips", type="json", nullable=true)
     *
     * @var string[]
     */
    protected $allowedIps = [];

    public function getAllowedIps(): ?array
    {
        return $this->allowedIps;
    }

    public function setAllowedIps(?array $allowedIps = null)
    {
        $this->allowedIps = $allowedIps;
    }
}
