<?php

namespace Innova\MediaResourceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Region.
 *
 * @ORM\Table(name="media_resource_region")
 * @ORM\Entity(repositoryClass="Innova\MediaResourceBundle\Repository\RegionRepository")
 */
class Region implements \JsonSerializable
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
     * @var float
     *
     * @ORM\Column(name="start", type="float")
     */
    private $start;

    /**
     * @var float
     *
     * @ORM\Column(name="end", type="float")
     */
    private $end;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;

    /**
     * @var MediaResource
     * @ORM\ManyToOne(targetEntity="Innova\MediaResourceBundle\Entity\MediaResource", inversedBy="regions")
     * @ORM\JoinColumn(name="media_resource_id", nullable=false)
     */
    private $mediaResource;

    /**
     * @var region configuration
     * @ORM\OneToOne(targetEntity="Innova\MediaResourceBundle\Entity\RegionConfig", mappedBy="region", cascade={"persist", "remove"})
     */
    private $regionConfig;

    /**
     * @var string
     *
     * @ORM\Column(name="uuid", type="string")
     */
    private $uuid;

    public function getId()
    {
        return $this->id;
    }

    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function setMediaResource(MediaResource $mr)
    {
        $this->mediaResource = $mr;

        return $this;
    }

    public function getMediaResource()
    {
        return $this->mediaResource;
    }

    public function setRegionConfig(RegionConfig $rc)
    {
        $this->regionConfig = $rc;

        return $this;
    }

    public function getRegionConfig()
    {
        return $this->regionConfig;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function __toString()
    {
        return $this->timeToHms($this->getStart()).' - '.$this->timeToHms($this->getEnd());
    }

    private function timeToHms($time)
    {
        $stringSec = strval($time);
        $fullMilli = explode('.', $stringSec);
        $milli = array_key_exists(1, $fullMilli) ?  substr($fullMilli[1], 0, 2) : '00';
        $ms = \gmdate('i:s', $time);

        return $ms.':'.$milli;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'helps' => $this->regionConfig,
            'start' => $this->start,
            'end' => $this->end,
            'note' => $this->note,
        ];
    }
}
