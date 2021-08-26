<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\QuotaRepository")
 * @ORM\Table(name="claro_cursusbundle_quota", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique", columns={"organization_id"})
 * })
 */
class Quota
{
    use Id;
    use Uuid;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization")
     * @ORM\JoinColumn(name="organization_id", nullable=false, onDelete="CASCADE")
     *
     * @var Organization
     */
    private $organization;

    /**
     * @ORM\Column(type="float")
     *
     * @var float
     */
    private $threshold = 0.0;

    /**
     * @ORM\Column(name="use_quotas", type="boolean")
     *
     * @var bool
     */
    private $useQuotas = false;

    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return float
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    public function setThreshold(float $threshold)
    {
        $this->threshold = $threshold;
    }

    /**
     * @return bool
     */
    public function useQuotas()
    {
        return $this->useQuotas;
    }

    public function setUseQuotas(float $useQuotas)
    {
        $this->useQuotas = $useQuotas;
    }
}
