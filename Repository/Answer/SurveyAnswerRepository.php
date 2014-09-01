<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Repository\Answer;

use Claroline\CoreBundle\Entity\User;
use Claroline\SurveyBundle\Entity\Survey;
use Doctrine\ORM\EntityRepository;

class SurveyAnswerRepository extends EntityRepository
{
    public function findSurveyAnswerBySurveyAndUser(
        Survey $survey,
        User $user,
        $executeQuery = true
    )
    {
        $dql = "
            SELECT sa
            FROM Claroline\SurveyBundle\Entity\Answer\SurveyAnswer sa
            WHERE sa.survey = :survey
            AND sa.user = :user
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('survey', $survey);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }
}
