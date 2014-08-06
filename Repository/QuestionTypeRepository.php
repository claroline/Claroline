<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Repository;

use Doctrine\ORM\EntityRepository;

class QuestionTypeRepository extends EntityRepository
{
    public function findAllQuestionTypes($executeQuery)
    {
        $dql = "
            SELECT qt
            FROM Claroline\SurveyBundle\Entity\QuestionType qt
            ORDER BY qt.name ASC
        ";
        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }
}