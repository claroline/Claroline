<?php

namespace HeVinci\UrlBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use HeVinci\UrlBundle\Validator\Constraints as UrlAssert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_url")
 */
class Url extends AbstractResource
{
    /**
     * @ORM\Column(name="url", length=255)
     * @UrlAssert\ReachableUrl
     */
    protected $url;

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }
}