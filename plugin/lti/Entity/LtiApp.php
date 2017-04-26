<?php

namespace UJM\LtiBundle\Entity;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="UJM\LtiBundle\Repository\LtiAppRepository")
 * @ORM\Table(name="ujm_lti_app")
 */
class LtiApp
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $appkey;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $secret;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     inversedBy="LtiApp"
     * )
     * @ORM\JoinTable(name="ujm_ltiapp_workspace")
     */
    private $workspaces;

    /**
     * Constructs a new instance of workspaces.
     */
    public function __construct()
    {
        $this->workspaces = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $appkey
     */
    public function setAppkey($appkey)
    {
        $this->appkey = $appkey;
    }

    /**
     * @return string
     */
    public function getAppkey()
    {
        return $this->appkey;
    }

    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return ArrayCollection
     */
    public function getWorkspaces()
    {
        return $this->workspaces;
    }

    /**
     * @param Workspace $workspace
     */
    public function addWorkspace(Workspace $workspace)
    {
        $this->workspaces->add($workspace);
    }

    /**
     * @param Workspace $workspace
     */
    public function removeWorkspace(Workspace $workspace)
    {
        $this->workspaces->removeElement($workspace);
    }
}
