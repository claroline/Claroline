<?php

namespace UJM\ExoBundle\Entity\Item;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Library\Attempt\PenaltyItemInterface;
use UJM\ExoBundle\Library\Model\ContentTrait;
use UJM\ExoBundle\Library\Model\PenaltyTrait;

/**
 * Hint.
 */
#[ORM\Table(name: 'ujm_hint')]
#[ORM\Entity]
class Hint implements PenaltyItemInterface
{
    use Id;
    use ContentTrait;
    use PenaltyTrait;
    use Uuid;

    /**
     * @var Item
     */
    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'hints')]
    private ?Item $question = null;

    public function __construct()
    {
        $this->refreshUuid();
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
