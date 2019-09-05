<?php

namespace HeVinci\UrlBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="hevinci_url")
 */
class Url extends AbstractResource
{
    const OPEN_IFRAME = 'iframe';
    const OPEN_REDIRECT = 'redirect';
    const OPEN_TAB = 'tab';

    /**
     * @ORM\Column(name="url", length=255)
     */
    protected $url;

    /**
     * @ORM\Column(type="string")
     */
    protected $mode = 'redirect';

    /**
     * @ORM\Column(type="boolean", name="internal_url")
     */
    protected $internalUrl = false;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $ratio = 56.25;

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setInternalUrl($internalUrl)
    {
        $this->internalUrl = $internalUrl;
    }

    public function getInternalUrl()
    {
        return $this->internalUrl;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
    }

    public function getRatio()
    {
        return $this->ratio;
    }
}
