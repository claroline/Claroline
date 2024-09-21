<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\MatchQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * Label.
 */
#[ORM\Table(name: 'ujm_association')]
#[ORM\Entity]
class Association implements AnswerPartInterface
{
    use Id;
    use ScoreTrait;
    use FeedbackTrait;

    #[ORM\JoinColumn(name: 'match_question_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: MatchQuestion::class, inversedBy: 'associations')]
    private $question;

    #[ORM\JoinColumn(name: 'label_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Label::class)]
    private $label;

    #[ORM\JoinColumn(name: 'proposal_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Proposal::class)]
    private $proposal;

    /**
     * Get MatchQuestion.
     *
     * @return MatchQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set MatchQuestion.
     */
    public function setQuestion(MatchQuestion $question)
    {
        $this->question = $question;
    }

    /**
     * Set label.
     */
    public function setLabel(Label $label)
    {
        $this->label = $label;
    }

    /**
     * Get label.
     *
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set proposal.
     */
    public function setProposal(Proposal $proposal)
    {
        $this->proposal = $proposal;
    }

    /**
     * Get proposal.
     *
     * @return Proposal
     */
    public function getProposal()
    {
        return $this->proposal;
    }
}
