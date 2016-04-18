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

class CardLearningRepository extends EntityRepository
{
    /**
     * Return the cards that must be studied at a given date for a given
     * user and a given deck.
     *
     * @param Deck $deck
     * @param User $user
     * @param \DateTime $date
     * @return array
     */
    public function findCardToReview(Deck $deck, User $user, \DateTime $date)
    {
        $dql = '
            SELECT cl
            FROM Claroline\FlashCardBundle\Entity\CardLearning cl
            JOIN cl.card c
            JOIN c.note n
            WHERE cl.user = :user
            AND n.deck = :deck
            AND cl.dueDate <= :date
            AND cl.painfull != 1
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameters([
            'user' => $user,
            'deck' => $deck,
            'date' => $date
        ]);

        return $query->getResult();
        //return $query->getArrayCardLearning();
    }
}
