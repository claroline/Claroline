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
use Claroline\FlashCardBundle\Entity\Card;
use Doctrine\ORM\EntityRepository;

class CardLogRepository extends EntityRepository
{
    /**
     * Return the last cardLog for a given card and for a specified
     * user.
     *
     * @param Card $card
     * @param User $user
     *
     * @return array
     */
    public function findOneByCardAndUserOrderByDate(Card $card, User $user)
    {
        $dql = '
            SELECT cl
            FROM Claroline\FlashCardBundle\Entity\CardLog cl
            WHERE cl.user = :user
            AND cl.card = :card
            ORDER BY cl.date
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameters([
            'card' => $card,
            'user' => $user,
        ]);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }
}
