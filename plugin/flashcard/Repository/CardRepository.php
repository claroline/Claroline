<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\FlashCardBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\FlashCardBundle\Entity\Deck;
use Doctrine\ORM\EntityRepository;

class CardRepository extends EntityRepository
{
    /**
     * Return the number of cards in a deck.
     *
     * @param Deck $deck
     *
     * @return int
     */
    public function countCards(Deck $deck)
    {
        $dql = '
            SELECT COUNT(c)
            FROM Claroline\FlashCardBundle\Entity\Card c
            JOIN c.note n
            WHERE n.deck = :deck
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameters([
            'deck' => $deck,
        ]);

        return $query->getSingleScalarResult();
    }

    /**
     * Return the cards that the given user has never studied before.
     *
     * @param Deck $deck
     * @param User $user
     * @param int  $maxResults
     *
     * @return array
     */
    public function findNewCardToLearn(Deck $deck, User $user, $maxResults = -1)
    {
        $dql = '
            SELECT c
            FROM Claroline\FlashCardBundle\Entity\Card c
            JOIN c.note n
            WHERE n.deck = :deck
            AND NOT EXISTS (
                SELECT cl
                FROM Claroline\FlashCardBundle\Entity\CardLearning cl
                WHERE cl.card = c
                AND cl.user = :user
            )
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameters([
            'user' => $user,
            'deck' => $deck,
        ]);

        if ($maxResults >= 0) {
            $query->setMaxResults($maxResults);
        }

        return $query->getResult();
    }

    /**
     * Return the cards that must be studied at a given date for a given
     * user and a given deck.
     *
     * @param Deck      $deck
     * @param User      $user
     * @param \DateTime $date
     * @param int       $maxResults
     *
     * @return array
     */
    public function findCardToReview(Deck $deck, User $user, \DateTime $date, $maxResults = -1)
    {
        $dql = '
            SELECT c
            FROM Claroline\FlashCardBundle\Entity\Card c
            JOIN c.note n
            JOIN Claroline\FlashCardBundle\Entity\CardLearning cl
            WITH cl.card = c.id
            WHERE cl.user = :user
            AND n.deck = :deck
            AND cl.dueDate <= :date
            AND cl.painful != 1
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameters([
            'user' => $user,
            'deck' => $deck,
            'date' => $date,
        ]);

        if ($maxResults >= 0) {
            $query->setMaxResults($maxResults);
        }

        return $query->getResult();
    }
}
