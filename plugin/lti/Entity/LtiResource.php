<?php

namespace UJM\LtiBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="UJM\LtiBundle\Repository\LtiResourceRepository")
 * @ORM\Table(name="ujm_lti_resource")
 */
class LtiResource extends AbstractResource
{
    /**
     * @ORM\ManyToOne(targetEntity="LtiApp")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ltiApp;

    /**
     * @ORM\Column(type="boolean")
     */
    private $openInNewTab;

    /**
     * @param LtiApp $ltiApp
     */
    public function setLtiApp($ltiApp)
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
}
