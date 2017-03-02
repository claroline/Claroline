<?php

namespace UJM\ExoBundle\Library\Model;

/**
 * Gives an entity the ability to have a feedback.
 */
trait FeedbackTrait
{
    /**
     * Feedback content.
     *
     * @var string
     *
     * @ORM\Column(name="feedback", type="text", nullable=true)
     */
    private $feedback = null;

    /**
     * Sets feedback.
     *
     * @param string $feedback
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * Gets feedback.
     *
     * @return string
     */
    public function getFeedback()
    {
        return $this->feedback;
    }
}
