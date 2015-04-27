<?php
/**
 * This file is part of the Claroline Connect package
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/22/15
 */

namespace Icap\SocialmediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ShareAction
 * @package Icap\SocialmediaBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="icap__socialmedia_share")
 */
class ShareAction extends ActionBase
{
    /**
     * @var string $network
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $network = null;

    /**
     * @return string
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * @param string $network
     * @return $this
     */
    public function setNetwork($network)
    {
        $this->network = $network;

        return $this;
    }
} 