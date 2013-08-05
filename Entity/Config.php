<?php

namespace Claroline\RssReaderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\RssReaderBundle\Validator\Constraints as CustomAssert;

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
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    protected $user;

    /**
     * @Assert\NotBlank()
     * @CustomAssert\IsRss()
     * @ORM\Column()
     */
    protected $url;

    /**
     * @ORM\Column(name="is_default", type="boolean")
     */
    protected $isDefault = false;

    /**
     * @ORM\Column(name="is_desktop", type="boolean")
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

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
