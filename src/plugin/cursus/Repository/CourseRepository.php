<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Repository;

use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Session;
use Doctrine\ORM\EntityRepository;

class CourseRepository extends EntityRepository
{
    public function search(string $search, int $nbResults)
    {
        return $this->createQueryBuilder('c')
            ->where('(UPPER(c.name) LIKE :search OR UPPER(c.code) LIKE :search)')
            ->andWhere('c.hidden = false')
            ->setFirstResult(0)
            ->setMaxResults($nbResults)
            ->setParameter('search', '%'.strtoupper($search).'%')
            ->getQuery()
            ->getResult();
    }

    public function isFullyRegistered(Course $course, User $user): bool
    {
        $dql = '
            SELECT COUNT(DISTINCT c) as count
            FROM Claroline\CursusBundle\Entity\Course AS c
            WHERE c = :course AND (EXISTS (
              SELECT su.id
              FROM Claroline\CursusBundle\Entity\Registration\SessionUser AS su
              LEFT JOIN su.session AS s
              WHERE s.course = c
                AND su.user = :user
                AND su.validated = 1
                AND su.confirmed = 1
            )
            OR EXISTS (
              SELECT sg.id
              FROM Claroline\CursusBundle\Entity\Registration\SessionGroup AS sg
              LEFT JOIN sg.group AS g
              LEFT JOIN g.users AS gu
              LEFT JOIN sg.session AS s2
              WHERE s2.course = c
                AND gu = :user
            ))
        ';

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameters([
                'course' => $course,
                'user' => $user,
            ]);

        return 0 < (int) $query->getSingleScalarResult();
    }

    public function findByWorkspace(Workspace $workspace)
    {
        $dql = '
            SELECT DISTINCT c
            FROM Claroline\CursusBundle\Entity\Course AS c
            LEFT JOIN c.sessions AS s
            WHERE c.workspace = :workspace
               OR s.workspace = :workspace
        ';

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('workspace', $workspace);

        return $query->getResult() ?? [];
    }

    public function countParticipants(Course $course): array
    {
        return [
            'tutors' => $this->countTutors($course),
            'learners' => $this->countLearners($course),
            'pending' => $this->countPending($course),
        ];
    }

    public function getRegistrationStats(Course $course, Session $session = null): array
    {
        // count the total participants
        $total = $this->countUsers($course, AbstractRegistration::LEARNER, $session);

        // the list of custom field types we are able to do "stats" on it
        $supportedTypes = [
            FieldFacet::BOOLEAN_TYPE,
            FieldFacet::NUMBER_TYPE,
            FieldFacet::COUNTRY_TYPE,
            FieldFacet::BOOLEAN_TYPE,
            FieldFacet::CHOICE_TYPE,
            FieldFacet::CASCADE_TYPE,
        ];

        $fields = [];
        foreach ($course->getPanelFacets() as $panelFacet) {
            /** @var FieldFacet $field */
            foreach ($panelFacet->getFieldsFacet() as $field) {
                if (in_array($field->getType(), $supportedTypes)) {
                    $fields[] = $field;
                }
            }
        }

        usort($fields, function (FieldFacet $fieldA, FieldFacet $fieldB) {
            return $fieldA <=> $fieldB;
        });

        return [
            'total' => $total,
            'fields' => array_map(function (FieldFacet $field) use ($course, $session) {
                return [
                    'field' => $field,
                    'values' => $this->getRegistrationFieldStats($field, $course, $session),
                ];
            }, $fields),
        ];
    }

    private function getRegistrationFieldStats(FieldFacet $field, Course $course, Session $session = null): array
    {
        $dql = '
            SELECT COUNT(ffv) as count, ffv.value
            FROM Claroline\CursusBundle\Entity\Registration\SessionUser AS su
            LEFT JOIN su.facetValues AS ffv
            LEFT JOIN su.user AS u
            LEFT JOIN su.session AS s
            WHERE ffv.fieldFacet = :field
              AND s.course = :course
              AND (su.type = :learnerType AND su.confirmed = 1 AND su.validated = 1)
              AND (u.isEnabled = true AND u.isRemoved = false AND u.technical = false)
        ';

        if ($session) {
            $dql .= ' AND su.session = :session';
        }

        $dql .= ' GROUP BY ffv.value';

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('course', $course)
            ->setParameter('learnerType', AbstractRegistration::LEARNER)
            ->setParameter('field', $field);

        if ($session) {
            $query->setParameter('session', $session);
        }

        return $query->getResult() ?? [];
    }

    public function countTutors(Course $course): int
    {
        return $this->countUsers($course, AbstractRegistration::TUTOR);
    }

    public function countLearners(Course $course): int
    {
        $count = $this->countUsers($course, AbstractRegistration::LEARNER);

        // add groups count
        $sessionGroups = $this->getEntityManager()
            ->createQuery('
                SELECT sg 
                FROM Claroline\CursusBundle\Entity\Registration\SessionGroup AS sg
                LEFT JOIN sg.session AS s
                WHERE sg.type = :registrationType
                  AND s.course = :course
            ')
            ->setParameters([
                'registrationType' => AbstractRegistration::LEARNER,
                'course' => $course,
            ])
            ->getResult();

        foreach ($sessionGroups as $sessionGroup) {
            $count += $sessionGroup->getGroup()->getUsers()->count();
        }

        return $count;
    }

    public function countPending(Course $course): int
    {
        // TODO : add CourseUser

        return (int) $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(DISTINCT su) 
                FROM Claroline\CursusBundle\Entity\Registration\SessionUser AS su
                LEFT JOIN su.user AS u
                LEFT JOIN su.session AS s
                WHERE su.type = :registrationType
                  AND s.course = :course
                  AND (su.confirmed = 0 OR su.validated = 0)
                  AND u.isEnabled = true AND u.isRemoved = false AND u.technical = false
            ')
            ->setParameters([
                'registrationType' => AbstractRegistration::LEARNER,
                'course' => $course,
            ])
            ->getSingleScalarResult();
    }

    private function countUsers(Course $course, string $type, Session $session = null): int
    {
        $dql = '
            SELECT COUNT(su) as count
            FROM Claroline\CursusBundle\Entity\Registration\SessionUser AS su
            LEFT JOIN su.session AS s
            LEFT JOIN su.user AS u
            WHERE s.course = :course
              AND (su.type = :type AND su.confirmed = 1 AND su.validated = 1)
              AND (u.isEnabled = true AND u.isRemoved = false AND u.technical = false)
        ';

        if ($session) {
            $dql .= ' AND su.session = :session';
        }

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('course', $course)
            ->setParameter('type', $type);

        if ($session) {
            $query->setParameter('session', $session);
        }

        return (int) $query->getSingleScalarResult();
    }

    public function findNamesWithPrefix(string $prefix): array
    {
        return array_map(
            function (array $course) {
                return $course['name'];
            },
            $this->getEntityManager()->createQuery('
                SELECT c.name
                FROM Claroline\CursusBundle\Entity\Course c
                WHERE c.name LIKE :search
            ')
                ->setParameter('search', addcslashes($prefix, '%_').'%')
                ->getResult()
        );
    }
}
