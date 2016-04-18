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
     * Return the cards that the given user has never studied before.
     *
     * @param Deck $deck
     * @param User $user
     * @param integer $maxResults
     * @return array
     */
    public function findNewCardToLearn(Deck $deck, User $user, $maxResults=-1)
    {
        $dql = '
            SELECT c
            FROM Claroline\FlashCardBundle\Entity\Card c
            JOIN Claroline\FlashCardBundle\Entity\Note n
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
            'deck' => $deck
        ]);
        if($maxResults >= 0) {
            $query->setMaxResults($maxResults);
        }

        return $query->getResult();
        //return $query->getArrayCard();
    }
}
