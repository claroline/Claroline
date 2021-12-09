<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro__open_badge_revocation_list")
 */
class RevocationList
{
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $issuer;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\OpenBadgeBundle\Entity\Assertion")
     */
    private $revokedAssertions;

    public function __construct()
    {
        $this->revokedAssertions = new ArrayCollection();
    }

    public function getIssuer()
    {
        return $this->issuer;
    }
}
