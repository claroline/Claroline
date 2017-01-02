<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\FlashCardBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\FlashCardBundle\Entity\Card;
use Claroline\FlashCardBundle\Entity\Deck;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * @DI\Service("claroline.flashcard.card_manager")
 */
class CardManager
{
    private $om;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "templating" = @DI\Inject("templating")
     * })
     *
     * @param ObjectManager   $om
     * @param EngineInterface $templating
     */
    public function __construct(ObjectManager $om, EngineInterface $templating)
    {
        $this->om = $om;
        $this->templating = $templating;
    }

    /**
     * @param Card $card
     */
    public function delete(Card $card)
    {
        $this->om->remove($card);
        $this->om->flush();
    }

    /**
     * @param Deck $deck
     *
     * @return int
     */
    public function countCards(Deck $deck)
    {
        $repoCard = $this->om->getRepository('ClarolineFlashCardBundle:Card');

        return $repoCard->countCards($deck);
    }

    /**
     * @param Deck $deck
     * @param User $user
     *
     * @return array
     */
    public function getNewCardToLearn(Deck $deck, User $user, $maxResults = -1)
    {
        $repoCard = $this->om->getRepository('ClarolineFlashCardBundle:Card');

        return $repoCard->findNewCardToLearn($deck, $user, $maxResults);
    }

    /**
     * @param Deck      $deck
     * @param User      $user
     * @param \DateTime $date
     *
     * @return array
     */
    public function getCardToReview(Deck $deck, User $user, \DateTime $date, $maxResults = -1)
    {
        $repoCard = $this->om->getRepository('ClarolineFlashCardBundle:Card');

        return $repoCard->findCardToReview($deck, $user, $date, $maxResults);
    }
}
