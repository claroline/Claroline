<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Order;
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
    #[ORM\ManyToOne(targetEntity: \UJM\ExoBundle\Entity\ItemType\MatchQuestion::class, inversedBy: 'labels')]
    private $interactionMatching;

    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get InteractionMatching.
     *
     * @return MatchQuestion
     */
    public function getInteractionMatching()
    {
        return $this->interactionMatching;
    }

    /**
     * Set InteractionMatching.
     */
    public function setInteractionMatching(MatchQuestion $interactionMatching)
    {
        $this->interactionMatching = $interactionMatching;
    }
}
