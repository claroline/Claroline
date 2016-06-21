<?php

namespace HeVinci\UrlBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;
use HeVinci\UrlBundle\Validator\Constraints as UrlAssert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="hevinci_url")
 */
class Url extends AbstractResource
{
    /**
     * @ORM\Column(name="url", length=255)
     * @UrlAssert\ReachableUrl
     */
    protected $url;

    /**
     * @ORM\Column(type="boolean", name="internal_url")
     */
    protected $internalUrl = false;

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
}
