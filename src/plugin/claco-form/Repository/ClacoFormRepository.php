<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Repository;

use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Doctrine\ORM\EntityRepository;

class ClacoFormRepository extends EntityRepository
{
    public function getEntryStats(ClacoForm $clacoForm): array
    {
        // the list of custom field types we are able to do "stats" on it
        $supportedTypes = [
            FieldFacet::BOOLEAN_TYPE,
            FieldFacet::NUMBER_TYPE,
            FieldFacet::COUNTRY_TYPE,
            FieldFacet::BOOLEAN_TYPE,
            FieldFacet::CHOICE_TYPE,
            FieldFacet::CASCADE_TYPE,
            FieldFacet::DATE_TYPE,
            FieldFacet::HTML_TYPE,
            FieldFacet::TEXT_TYPE,
        ];

        $fields = [];
        foreach ($clacoForm->getFields() as $field) {
            if (in_array($field->getFieldFacet()->getType(), $supportedTypes)) {
                $fields[] = $field->getFieldFacet();
            }
        }

        usort($fields, function (FieldFacet $fieldA, FieldFacet $fieldB) {
            return $fieldB <=> $fieldA;
        });

        return [
            'total' => $this->countEntries($clacoForm),
            'users' => $this->countParticipants($clacoForm),
            'fields' => array_map(function (FieldFacet $field) use ($clacoForm) {
                return [
                    'field' => $field,
                    'values' => $this->getEntryFieldStats($field, $clacoForm),
                ];
            }, $fields),
        ];
    }

    private function getEntryFieldStats(FieldFacet $field, ClacoForm $clacoForm): array
    {
        $dql = '
            SELECT COUNT(ffv) as count, ffv.value
            FROM Claroline\ClacoFormBundle\Entity\Entry AS e
            LEFT JOIN e.fieldValues AS fv
            LEFT JOIN fv.fieldFacetValue AS ffv
            LEFT JOIN e.user AS u
            WHERE ffv.fieldFacet = :field
              AND e.clacoForm = :clacoForm
              AND (u.disabled = false AND u.isRemoved = false AND u.technical = false)
            GROUP BY ffv.value
        ';

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('clacoForm', $clacoForm)
            ->setParameter('field', $field);

        return $query->getResult() ?? [];
    }

    private function countParticipants(ClacoForm $clacoForm): int
    {
        return (int) $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(DISTINCT u) 
                FROM Claroline\ClacoFormBundle\Entity\Entry AS e
                LEFT JOIN e.user AS u
                WHERE e.clacoForm = :clacoForm
                  AND (u.disabled = false AND u.isRemoved = false AND u.technical = false)
            ')
            ->setParameters([
                'clacoForm' => $clacoForm,
            ])
            ->getSingleScalarResult();
    }

    private function countEntries(ClacoForm $clacoForm): int
    {
        return (int) $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(e) 
                FROM Claroline\ClacoFormBundle\Entity\Entry AS e
                LEFT JOIN e.user AS u
                WHERE e.clacoForm = :clacoForm
                  AND (u.disabled = false AND u.isRemoved = false AND u.technical = false)
            ')
            ->setParameters([
                'clacoForm' => $clacoForm,
            ])
            ->getSingleScalarResult();
    }
}
