<?php

namespace Innova\MediaResourceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * RegionConfig.
 *
 * @ORM\Table(name="media_resource_region_config")
 * @ORM\Entity
 */
class RegionConfig implements \JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * enable / disable play in loop option.
     *
     * @var loop
     *
     * @ORM\Column(name="has_loop", type="boolean")
     */
    private $loop;

    /**
     * enable / disable backward building option.
     *
     * @var backward
     *
     * @ORM\Column(name="has_backward", type="boolean")
     */
    private $backward;

    /**
     * enable / disable play slower option.
     *
     * @var rate
     * @ORM\Column(name="has_rate", type="boolean")
     */
    private $rate;

    /**
     * @var region
     * @ORM\OneToOne(targetEntity="Innova\MediaResourceBundle\Entity\Region", inversedBy="regionConfig")
     * @ORM\JoinColumn(nullable=false)
     */
    private $region;

    /**
     * @ORM\OneToMany(targetEntity="Innova\MediaResourceBundle\Entity\HelpText", cascade={"remove", "persist"}, mappedBy="regionConfig")
     */
    private $helpTexts;

    /**
     * @ORM\OneToMany(targetEntity="Innova\MediaResourceBundle\Entity\HelpLink", cascade={"remove", "persist"}, mappedBy="regionConfig")
     */
    private $helpLinks;

    /**
     * @var related region for help
     *              User can be helped by another region content
     * @ORM\Column(name="help_region_uuid", type="string", length=255)
     */
    private $helpRegionUuid;

    public function __construct()
    {
        $this->helpTexts = new ArrayCollection();
        $this->helpLinks = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setLoop($loop)
    {
        $this->loop = $loop;

        return $this;
    }

    public function isLoop()
    {
        return $this->loop;
    }

    public function setBackward($backward)
    {
        $this->backward = $backward;

        return $this;
    }

    public function isBackward()
    {
        return $this->backward;
    }

    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    public function isRate()
    {
        return $this->rate;
    }

    public function addHelpText(HelpText $helpText)
    {
        $this->helpTexts[] = $helpText;

        return $this;
    }

    public function removeHelpText(HelpText $helpText)
    {
        $this->helpTexts->removeElement($helpText);

        return $this;
    }

    public function getHelpTexts()
    {
        return $this->helpTexts;
    }

    public function addHelpLink(HelpLink $helpLink)
    {
        $this->helpLinks[] = $helpLink;

        return $this;
    }

    public function removeHelpLink(HelpLink $helpLink)
    {
        $this->helpLinks->removeElement($helpLink);

        return $this;
    }

    public function getHelpLinks()
    {
        return $this->helpLinks;
    }

    public function setRegion(Region $region)
    {
        $this->region = $region;

        return $this;
    }

    public function getRegion()
    {
        return $this->region;
    }

    public function getHelpRegionUuid()
    {
        return $this->helpRegionUuid;
    }

    public function setHelpRegionUuid($helpRegionUuid)
    {
        $this->helpRegionUuid = $helpRegionUuid;

        return $this;
    }

    public function jsonSerialize()
    {
        $links = [];
        foreach ($this->helpLinks as $link) {
            $links[] = $link->jsonSerialize();
        }

        $texts = [];
        foreach ($this->helpTexts as $text) {
            $texts[] = $text->jsonSerialize();
        }

        return [
            'id' => $this->id,
            'loop' => $this->loop,
            'backward' => $this->backward,
            'rate' => $this->rate,
            'helpTexts' => $texts,
            'helpLinks' => $links,
            'helpRegionUuid' => $this->helpRegionUuid,
        ];
    }
}
