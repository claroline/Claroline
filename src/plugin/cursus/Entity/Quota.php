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
 * @ORM\Entity
 * @ORM\Table(name="claro_cursusbundle_quota", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique", columns={"organization_id"})
 * })
 */
class Quota
{
    use Id;
    use Uuid;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization"
     * )
     * @ORM\JoinColumn(name="organization_id", onDelete="CASCADE")
     * 
     * @var Organization
     */
    private $organization;

    /**
     * @ORM\Column(type="float")
     *
     * @var float
     */
    private $limit = 0.0;

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

    /**
     * @return float
     */
    public function getLimit()
    {
        return $this->limit;
    }

    public function setOrganisation(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function setLimit(float $limit)
    {
        $this->limit = $limit;
    }
}
