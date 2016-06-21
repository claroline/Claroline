<?php

namespace Innova\VideoRecorderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VideoRecorderConfiguration Entity.
 *
 * @ORM\Table(name="innova_video_recorder_configuration")
 * @ORM\Entity
 */
class VideoRecorderConfiguration
{
    /**
   * @var int
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @var float
   * Define the maximum time allowed per recording
   * @ORM\Column(name="max_recording_time", type="integer", options={"default" = 0})
   */
  protected $maxRecordingTime;

    public function getId()
    {
        return $this->id;
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
