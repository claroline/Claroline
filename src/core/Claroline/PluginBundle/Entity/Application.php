<?php

namespace Claroline\PluginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\PluginBundle\Entity\ApplicationLauncher;

/**
 * @ORM\Entity(repositoryClass="Claroline\PluginBundle\Repository\ApplicationRepository")
 * @ORM\Table(name="claro_application")
 */
class Application extends Plugin
{
    /**
     * @ORM\OneToMany(targetEntity="ApplicationLauncher", mappedBy="application", cascade={"persist", "remove"})
     */
    private $launchers;

    /**
     * @ORM\Column(name="index_route", type="string", length="255", nullable=true)
     */
    private $indexRoute;
    
    /**
     * @ORM\Column(name="is_eligible_for_platform_index", type="boolean")
     */
    private $isEligibleForPlatformIndex = false;
    
    /**
     * @ORM\Column(name="is_platform_index", type="boolean")
     */
    private $isPlatformIndex = false;
    
    /**
     * @ORM\Column(name="is_eligible_for_connection_target", type="boolean")
     */
    private $isEligibleForConnectionTarget = false;
    
    /**
     * @ORM\Column(name="is_connection_target", type="boolean")
     */
    private $isConnectionTarget = false;
    
    public function __construct()
    {
        $this->launchers = new ArrayCollection();
    }

    public function getLaunchers()
    {
        return $this->launchers->toArray();
    }

    public function addLauncher(ApplicationLauncher $launcher)
    {
        $this->launchers->add($launcher);
    }

    public function getIndexRoute()
    {
        return $this->indexRoute;
    }

    public function setIndexRoute($indexRoute)
    {
        $this->indexRoute = $indexRoute;
    }

    public function isEligibleForPlatformIndex()
    {
        return $this->isEligibleForPlatformIndex;
    }

    public function setEligibleForPlatformIndex($isEligibleForPlatformIndex)
    {
        $this->isEligibleForPlatformIndex = $isEligibleForPlatformIndex;
    }
    
    public function isPlatformIndex()
    {
        return $this->isPlatformIndex;
    }

    public function setIsPlatformIndex($isPlatformIndex)
    {
        $this->isPlatformIndex = $isPlatformIndex;
    }
    
    public function isEligibleForConnectionTarget()
    {
        return $this->isEligibleForConnectionTarget;
    }
    
    public function setEligibleForConnectionTarget($isEligibleForConnectionTarget)
    {
        $this->isEligibleForConnectionTarget = $isEligibleForConnectionTarget;
    }
    
    public function isConnectionTarget()
    {
        return $this->isConnectionTarget;
    }
    
    public function setIsConnectionTarget($isConnectionTarget)
    {
        $this->isConnectionTarget = $isConnectionTarget;
    }
}