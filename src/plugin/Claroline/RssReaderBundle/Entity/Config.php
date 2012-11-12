<?php

namespace Claroline\RssReaderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_rssreader_configuration")
 */
class Config
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace")
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    protected $workspace;

    /**
     * @ORM\Column(type="string", name="url")
     */
    protected $url;

    /**
     * @ORM\Column(type="boolean", name="is_default")
     */
    protected $isDefault = false;

    /**
     * @ORM\Column(type="boolean", name="is_desktop")
     */
    protected $isDesktop = false;

    public function getId()
    {
        return $this->id;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function isDefault()
    {
        return $this->isDefault;
    }

    public function isDesktop()
    {
        return $this->isDesktop;
    }

    public function setDefault($bool)
    {
        $this->isDefault = $bool;
    }

    public function setDesktop($bool)
    {
        $this->isDesktop = $bool;
    }

}
