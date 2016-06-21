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
     * Return all the cardLearning for a given user in a given deck.
     *
     * @param Deck $deck
     * @param User $user
     *
     * @return array
     */
    public function findByDeckAndUser(Deck $deck, User $user)
    {
        $dql = '
            SELECT cl
            FROM Claroline\FlashCardBundle\Entity\CardLearning cl
            JOIN cl.card c
            JOIN c.note n
            WHERE cl.user = :user
            AND n.deck = :deck
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameters([
            'user' => $user,
            'deck' => $deck,
        ]);

        return $query->getResult();
    }
}
