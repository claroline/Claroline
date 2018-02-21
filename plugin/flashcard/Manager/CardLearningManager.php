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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\FlashCardBundle\Entity\Card;
use Claroline\FlashCardBundle\Entity\CardLearning;
use Claroline\FlashCardBundle\Entity\Deck;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * @DI\Service("claroline.flashcard.card_learning_manager")
 */
class CardLearningManager
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
     * @param CardLearning $cardLearning
     *
     * @return CardLearning
     */
    public function save(CardLearning $cardLearning)
    {
        $this->om->persist($cardLearning);
        $this->om->flush();

        return $cardLearning;
    }

    /**
     * @param CardLearning $cardLearning
     */
    public function delete(CardLearning $cardLearning)
    {
        $this->om->remove($cardLearning);
        $this->om->flush();
    }

    /**
     * @param Card $card
     * @param User $user
     *
     * @return CardLearning
     */
    public function getCardLearning(Card $card, User $user)
    {
        $repo = $this->om->getRepository('ClarolineFlashCardBundle:CardLearning');

        return $repo->findOneBy(
            [
                'user' => $user,
                'card' => $card,
            ]
        );
    }

    /**
     * @param Deck $deck
     * @param User $user
     *
     * @return CardLearning
     */
    public function allCardLearning(Deck $deck, User $user)
    {
        $repo = $this->om->getRepository('ClarolineFlashCardBundle:CardLearning');

        return $repo->findByDeckAndUser($deck, $user);
    }
}
