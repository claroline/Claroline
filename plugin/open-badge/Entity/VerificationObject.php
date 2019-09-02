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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro__open_badge_verification_object")
 */
class VerificationObject
{
    use Id;

    /**
     * @ORM\Column()
     */
    private $verificationProperty;

    /**
     * @ORM\Column()
     */
    private $startWith;

    /**
     * @ORM\Column(type="json_array")
     */
    private $allowedOrigins;
}
