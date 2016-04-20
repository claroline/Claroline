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
 * @ORM\Table(name="claro_user_options")
 * @ORM\Entity()
 */
class UserOptions
{
    const READ_ONLY_MODE = 0;
    const EDITION_MODE = 1;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     mappedBy="options"
     * )
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(name="desktop_background_color", nullable=true)
     */
    protected $desktopBackgroundColor;

    /**
     * @ORM\Column(name="desktop_mode", type="integer", options={"default":1})
     */
    protected $desktopMode = 1;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $details;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getDesktopBackgroundColor()
    {
        return $this->desktopBackgroundColor;
    }

    public function setDesktopBackgroundColor($desktopBackgroundColor)
    {
        $this->desktopBackgroundColor = $desktopBackgroundColor;
    }

    public function getDesktopMode()
    {
        return $this->desktopMode;
    }

    public function setDesktopMode($desktopMode)
    {
        $this->desktopMode = $desktopMode;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }
}
