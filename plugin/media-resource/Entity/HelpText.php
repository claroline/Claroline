<?php

namespace Innova\MediaResourceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RegionConfig.
 *
 * @ORM\Table(name="media_resource_help_text")
 * @ORM\Entity
 */
class HelpText implements \JsonSerializable
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
     * @var text
     *
     * @ORM\Column(type="string")
     */
    private $text;

    /**
     * @var RegionConfig
     * @ORM\ManyToOne(targetEntity="Innova\MediaResourceBundle\Entity\RegionConfig", inversedBy="helpTexts")
     * @ORM\JoinColumn(name="region_config_id", nullable=false)
     */
    private $regionConfig;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setRegionConfig(RegionConfig $regionConfig)
    {
        $this->regionConfig = $regionConfig;

        return $this;
    }

    public function getRegionConfig()
    {
        return $this->regionConfig;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
        ];
    }
}
