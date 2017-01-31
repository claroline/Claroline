<?php

namespace UJM\ExoBundle\Library\Model;

/**
 * Gives an entity the ability to have a togglable shuffle mode.
 */
trait ShuffleTrait
{
    /**
     * Is shuffle enabled ?
     *
     * @var bool
     *
     * @ORM\Column(name="shuffle", type="boolean")
     */
    private $shuffle = false;

    /**
     * Sets shuffle.
     *
     * @param bool $shuffle
     */
    public function setShuffle($shuffle)
    {
        $this->shuffle = $shuffle;
    }

    /**
     * Gets shuffle.
     */
    public function getShuffle()
    {
        return $this->shuffle;
    }
}
