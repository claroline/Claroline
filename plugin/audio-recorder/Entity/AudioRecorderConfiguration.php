<?php

namespace Innova\AudioRecorderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AudioRecorderConfiguration Entity.
 *
 * @ORM\Table(name="innova_audio_recorder_configuration")
 * @ORM\Entity
 */
class AudioRecorderConfiguration
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var float
     *            Define the maximum time allowed per recording
     * @ORM\Column(name="max_recording_time", type="integer", options={"default" = 0})
     */
    protected $maxRecordingTime;

    /**
     * @var int
     *          Define maximum number of try
     * @ORM\Column(name="max_try", type="integer", options={"default" = 0})
     */
    protected $maxTry;

    public function getId()
    {
        return $this->id;
    }

    public function setMaxTry($max)
    {
        $this->maxTry = $max;

        return $this;
    }

    public function getMaxTry()
    {
        return $this->maxTry;
    }

    public function setMaxRecordingTime($max)
    {
        $this->maxRecordingTime = $max;

        return $this;
    }

    public function getMaxRecordingTime()
    {
        return $this->maxRecordingTime;
    }
}
