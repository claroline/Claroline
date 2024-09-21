<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\OpenQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * Keyword.
 */
#[ORM\Table(name: 'ujm_word_response')]
#[ORM\Entity]
class Keyword implements AnswerPartInterface
{
    use Id;
    use ScoreTrait;
    use FeedbackTrait;

    /**
     * @var string
     */
    #[ORM\Column(name: 'response', type: Types::STRING, length: 255)]
    private $text;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'caseSensitive', type: Types::BOOLEAN, nullable: true)]
    private $caseSensitive;

    /**
     * @deprecated this relation needs to be removed as it is not needed
     *
     * @var OpenQuestion
     */
    #[ORM\JoinColumn(name: 'interaction_open_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: OpenQuestion::class, inversedBy: 'keywords')]
    private $interactionopen;

    /**
     * @deprecated this relation needs to be removed as it is not needed
     *
     * @var Hole
     */
    #[ORM\JoinColumn(name: 'hole_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Hole::class, inversedBy: 'keywords')]
    private $hole;

    /**
     * Get text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set text.
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Is the keyword case sensitive ?
     *
     * @return bool
     */
    public function isCaseSensitive()
    {
        return $this->caseSensitive;
    }

    /**
     * Set caseSensitive.
     *
     * @param bool $caseSensitive
     */
    public function setCaseSensitive($caseSensitive)
    {
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * @deprecated this entity do not need to know open question as they also can be linked to holes
     *
     * @return OpenQuestion
     */
    public function getInteractionOpen()
    {
        return $this->interactionopen;
    }

    /**
     * @deprecated this entity do not need to know open question as they also can be linked to holes
     */
    public function setInteractionOpen(OpenQuestion $interactionOpen)
    {
        $this->interactionopen = $interactionOpen;
    }

    /**
     * @deprecated this entity do not need to know holes as they also can be linked to open questions
     *
     * @return Hole
     */
    public function getHole()
    {
        return $this->hole;
    }

    /**
     * @deprecated this entity do not need to know holes as they also can be linked to open questions
     */
    public function setHole(Hole $hole)
    {
        $this->hole = $hole;
    }
}
