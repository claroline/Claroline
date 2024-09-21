<?php

namespace Claroline\AppBundle\Entity\Restriction;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @deprecated the feature has been removed.
 */
trait AllowedIps
{
    /**
     * @var string[]
     */
    #[ORM\Column(name: 'allowed_ips', type: Types::JSON, nullable: true)]
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
