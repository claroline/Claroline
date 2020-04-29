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
     *
     * @var string
     */
    private $url;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $mode = self::OPEN_REDIRECT;

    /**
     * @ORM\Column(type="boolean", name="internal_url")
     *
     * @var bool
     */
    private $internalUrl = false;

    /**
     * @ORM\Column(type="float", nullable=true)
     *
     * @var float
     */
    private $ratio = 56.25;

    /**
     * Set url.
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set internal url.
     *
     * @param bool $internalUrl
     */
    public function setInternalUrl($internalUrl)
    {
        $this->internalUrl = $internalUrl;
    }

    /**
     * Is internal url ?
     *
     * @return bool
     */
    public function isInternalUrl()
    {
        return $this->internalUrl;
    }

    /**
     * Set opening mode.
     *
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Get opening mode.
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set display ratio for OPEN_IFRAME mode.
     *
     * @param float $ratio
     */
    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
    }

    /**
     * Get display ratio for OPEN_IFRAME mode.
     *
     * @return float
     */
    public function getRatio()
    {
        return $this->ratio;
    }
}
