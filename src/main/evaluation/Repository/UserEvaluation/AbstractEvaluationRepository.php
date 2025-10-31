<?php

namespace Claroline\EvaluationBundle\Repository\UserEvaluation;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Doctrine\ORM\EntityRepository;

abstract class AbstractEvaluationRepository extends EntityRepository
{
    abstract protected static function getSubjectProp(): string;

    public function countByStatus(object $subject, Organization $organization): array
    {
        $subjectProp = static::getSubjectProp();

        return $this->getEntityManager()
            ->createQuery("
                SELECT e.status, COUNT(e) AS value
                FROM {$this->getEntityName()} AS e
                LEFT JOIN e.user AS u
                LEFT JOIN u.userOrganizationReferences AS uo
                WHERE uo.organization = :organization 
                  AND e.$subjectProp = :subject
                  AND e.archived = false
                  AND u.disabled = false
                GROUP BY e.status
                ORDER BY value DESC
            ")
            ->setParameter('organization', $organization)
            ->setParameter('subject', $subject)
            ->getResult();
    }

    public function findCompletionStats(object $subject, Organization $organization): array
    {
        $subjectProp = static::getSubjectProp();

        $stats = $this->getEntityManager()
            ->createQuery("
                SELECT AVG(e.progression) AS avg, MAX(e.progression) AS max, MIN(e.progression) AS min
                FROM {$this->getEntityName()} AS e
                LEFT JOIN e.user AS u
                LEFT JOIN u.userOrganizationReferences AS uo
                WHERE uo.organization = :organization 
                  AND e.$subjectProp = :subject
                  AND e.archived = false
                  AND u.disabled = false
           ")
            ->setParameter('organization', $organization)
            ->setParameter('subject', $subject)
            ->getSingleResult();

        $progression = [];

        $groups = [20, 40, 60, 80, 100];
        foreach ($groups as $i => $group) {
            $parameters = [
                'subject' => $subject,
                'organization' => $organization,
            ];

            $sql = "
                SELECT COUNT(DISTINCT e)
                FROM {$this->getEntityName()} AS e
                LEFT JOIN e.user AS u
                LEFT JOIN u.userOrganizationReferences AS uo
                WHERE uo.organization = :organization
                  AND e.$subjectProp = :subject
                  AND e.archived = false
                  AND u.disabled = false
            ";

            if (0 === $i) {
                $sql .= 'AND e.progression <= :max';
                $parameters['max'] = $group;
            } elseif (count($groups) - 1 === $i) {
                $sql .= 'AND e.progression >= :min';
                $parameters['min'] = $groups[$i - 1];
            } else {
                $sql .= 'AND e.progression >= :min AND e.progression < :max';
                $parameters['min'] = $groups[$i - 1];
                $parameters['max'] = $group;
            }

            $progression[] = [
                'value' => $group,
                'users' => (int) $this->getEntityManager()
                    ->createQuery($sql)
                    ->setParameters($parameters)
                    ->getSingleScalarResult(),
            ];
        }

        return [
            'stats' => $stats,
            'progression' => $progression,
        ];
    }

    public function findScoreStats(object $subject, Organization $organization): array
    {
        $subjectProp = static::getSubjectProp();

        $stats = $this->getEntityManager()
            ->createQuery("
                SELECT AVG(e.score / e.scoreMax) AS avg, MAX(e.score / e.scoreMax) AS max, MIN(e.score / e.scoreMax) AS min
                FROM {$this->getEntityName()} AS e
                LEFT JOIN e.user AS u
                LEFT JOIN u.userOrganizationReferences AS uo
                WHERE uo.organization = :organization 
                  AND e.$subjectProp = :subject
                  AND e.archived = false
                  AND e.status IN (:statuses)
                  AND e.scoreMax IS NOT NULL
                  AND e.scoreMax != 0
                  AND u.disabled = false
           ")
            ->setParameter('organization', $organization)
            ->setParameter('subject', $subject)
            // the score is not calculated till the evaluation is completed
            ->setParameter('statuses', [EvaluationStatus::COMPLETED, EvaluationStatus::FAILED, EvaluationStatus::PASSED])
            ->getSingleResult();

        $scores = $this->getEntityManager()
            ->createQuery("
                SELECT u.username AS user, (e.score / e.scoreMax) AS score
                FROM {$this->getEntityName()} AS e
                LEFT JOIN e.user AS u
                LEFT JOIN u.userOrganizationReferences AS uo
                WHERE uo.organization = :organization 
                  AND e.$subjectProp = :subject
                  AND e.status IN (:statuses)
                  AND e.scoreMax IS NOT NULL
                  AND e.scoreMax != 0
                  AND u.disabled = false
           ")
            ->setParameter('organization', $organization)
            ->setParameter('subject', $subject)
            // the score is not calculated till the evaluation is completed
            ->setParameter('statuses', [EvaluationStatus::COMPLETED, EvaluationStatus::FAILED, EvaluationStatus::PASSED])
            ->getArrayResult();

        return [
            'stats' => $stats,
            'scores' => $scores,
        ];
    }

    public function findCompletion(object $subject, Organization $organization): array
    {
        $statuses = $this->countByStatus($subject, $organization);

        $count = 0;
        $total = 0;
        foreach ($statuses as $status) {
            if (EvaluationStatus::isTerminated($status['status'])) {
                $count += $status['value'];
            }

            $total += $status['value'];
        }

        return [
            'count' => $count,
            'total' => $total,
        ];
    }

    public function findSuccess(object $subject, Organization $organization): array
    {
        $statuses = $this->countByStatus($subject, $organization);

        $count = 0;
        $total = 0;
        foreach ($statuses as $status) {
            if (EvaluationStatus::PASSED === $status['status']) {
                $count += $status['value'];
            }

            if (EvaluationStatus::isTerminated($status['status'])) {
                $total += $status['value'];
            }
        }

        return [
            'count' => $count,
            'total' => $total,
        ];
    }
}
