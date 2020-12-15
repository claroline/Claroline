<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_session")
 */
class Session
{
    /**
     * @ORM\Id
     * @ORM\Column(name="session_id", type="string", length=255)
     */
    protected $id;

    /**
     * @ORM\Column(name="session_data", type="text")
     */
    protected $data;

    /**
     * @ORM\Column(name="session_time", type="integer", length=11)
     */
    protected $time;
}
