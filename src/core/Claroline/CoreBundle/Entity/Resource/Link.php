<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_link")
 */
class Link extends AbstractResource
{
    /**
     * @ORM\Column(type="string")
     */
    protected $url;

    /**
     * Returns the link url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the link url.
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}