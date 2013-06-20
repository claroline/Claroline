<?php

namespace Claroline\CoreBundle\Entity\Logger;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_log_desktop_widget_config")
 */
class LogDesktopWidgetConfig
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type="integer", name="amount", nullable=false)
     */
    protected $amount = 5;

    /**
     * Set amount
     *
     * @param integer $amount
     * @return LogDesktopWidgetConfig
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    
        return $this;
    }

    /**
     * Get amount
     *
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set user
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @return LogDesktopWidgetConfig
     */
    public function setUser(\Claroline\CoreBundle\Entity\User $user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Claroline\CoreBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}