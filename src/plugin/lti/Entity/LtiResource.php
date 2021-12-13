<?php

namespace UJM\LtiBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ujm_lti_resource")
 */
class LtiResource extends AbstractResource
{
    /**
     * @ORM\ManyToOne(targetEntity="LtiApp")
     * @ORM\JoinColumn(nullable=true)
     */
    private $ltiApp;

    /**
     * @ORM\Column(type="boolean")
     */
    private $openInNewTab = false;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $ratio = 56.25;

    /**
     * @param LtiApp $ltiApp
     */
    public function setLtiApp(LtiApp $ltiApp = null)
    {
        $this->ltiApp = $ltiApp;
    }

    /**
     * @return LtiApp
     */
    public function getLtiApp()
    {
        return $this->ltiApp;
    }

    /**
     * @return bool
     */
    public function getOpenInNewTab()
    {
        return $this->openInNewTab;
    }

    /**
     * @param bool $openInNewTab
     */
    public function setOpenInNewTab($openInNewTab)
    {
        $this->openInNewTab = $openInNewTab;
    }

    /**
     * @return float|null
     */
    public function getRatio()
    {
        return $this->ratio;
    }

    /**
     * @param float|null $ratio
     */
    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
    }
}
