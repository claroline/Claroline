<?php

namespace Claroline\CoreBundle\Entity\Tool;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\User;

class UserDesktopTool
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}, inversedBy="desktopTools"
     * )
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\Tool", cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="tool_id", referencedColumnName="id")
     *
     */
    private $tool;

    /**
     * @ORM\Column(name="order", type="integer")
     */
    protected $order;

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getTool()
    {
        return $this->tool;
    }

    public function setTool(Tool $tool)
    {
        $this->tool = $tool;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }
}

