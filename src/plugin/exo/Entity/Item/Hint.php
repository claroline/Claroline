<?php

namespace UJM\ExoBundle\Entity\Item;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Library\Attempt\PenaltyItemInterface;
use UJM\ExoBundle\Library\Model\ContentTrait;
use UJM\ExoBundle\Library\Model\PenaltyTrait;

/**
 * Hint.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_hint")
 */
class Hint implements PenaltyItemInterface
{
    use ContentTrait;
    use PenaltyTrait;
    use Uuid;
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="hints")
     *
     * @var Item
     */
    private $question;

    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setQuestion(Item $question)
    {
        $this->question = $question;
    }

    /**
     * @return Item
     */
    public function getQuestion()
    {
        return $this->question;
    }
}
