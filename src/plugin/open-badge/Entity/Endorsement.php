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
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro__open_badge_endorsement")
 */
class Endorsement
{
    use Uuid;
    use Id;

    /**
     * @ORM\Column(type="json_array")
     */
    private $claim;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $issuer;

    /**
     * @ORM\Column(type="datetime")
     */
    private $issuedOn;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\OpenBadgeBundle\Entity\VerificationObject")
     */
    private $verification;

    /**
     * @ORM\Column(type="text")
     */
    private $comment;

    public function __construct()
    {
        $this->refreshUuid();
    }
}
