<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\AppBundle\Entity\Display\Order;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\MatchQuestion;
use UJM\ExoBundle\Library\Model\ContentTrait;

/**
 * Label.
 */
#[ORM\Table(name: 'ujm_label')]
#[ORM\Entity]
class Label
{
    use Id;
    use ContentTrait;
    use Order;
    use Uuid;

    #[ORM\JoinColumn(name: 'interaction_matching_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: MatchQuestion::class, inversedBy: 'labels')]
    private ?MatchQuestion $interactionMatching = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getInteractionMatching(): ?MatchQuestion
    {
        return $this->interactionMatching;
    }

    public function setInteractionMatching(MatchQuestion $interactionMatching): void
    {
        $this->interactionMatching = $interactionMatching;
    }
}
