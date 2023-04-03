<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Finder\Registration;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CommunityBundle\Finder\Filter\UserFilter;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CursusBundle\Entity\Registration\CourseUser;
use Doctrine\ORM\QueryBuilder;

class CourseUserFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return CourseUser::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $userJoin = false;
        if (!array_key_exists('user', $searches)) {
            $qb->join('obj.user', 'u');
            $userJoin = true;

            // automatically excludes results for disabled/deleted users
            $this->addFilter(UserFilter::class, $qb, 'u', [
                'disabled' => in_array('userDisabled', array_keys($searches)) && $searches['userDisabled'],
            ]);
        }

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'course':
                    $qb->join('obj.course', 'c');
                    $qb->andWhere("c.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'user':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }

                    $qb->andWhere("u.uuid = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'organizations':
                    if (!$userJoin) {
                        $qb->join('obj.user', 'u');
                        $userJoin = true;
                    }

                    // get user organizations
                    $qb->leftJoin('u.userOrganizationReferences', 'ref');
                    $qb->leftJoin('ref.organization', 'o');

                    // get organizations from user groups
                    $qb->leftJoin('u.groups', 'g');
                    $qb->leftJoin('g.organizations', 'go');

                    $qb->andWhere('(o.uuid IN (:organizations) OR go.uuid IN (:organizations))');
                    $qb->setParameter('organizations', is_array($filterValue) ? $filterValue : [$filterValue]);

                    break;

                default:
                    if (false !== strpos($filterName, 'data.')) {
                        $filterName = str_replace('data.', '', $filterName);
                        $field = $this->om->getRepository(FieldFacet::class)->findOneBy(['uuid' => $filterName]);
                        if ($field) {
                            $this->filterField($qb, $filterName, $filterValue, $field);
                        }
                    } else {
                        $this->setDefaults($qb, $filterName, $filterValue);
                    }
            }
        }

        return $qb;
    }

    private function filterField(QueryBuilder $qb, $filterName, $filterValue, FieldFacet $field)
    {
        $parsedFilterName = str_replace('-', '', $filterName);

        $qb->leftJoin('obj.facetValues', "fv{$parsedFilterName}");
        $qb->leftJoin("fv{$parsedFilterName}.fieldFacet", "ff{$parsedFilterName}");
        $qb->andWhere("ff{$parsedFilterName}.uuid = :field{$parsedFilterName}");
        $qb->setParameter("field{$parsedFilterName}", $field->getUuid());

        switch ($field->getType()) {
            case FieldFacet::DATE_TYPE:
            case FieldFacet::BOOLEAN_TYPE:
            case FieldFacet::NUMBER_TYPE:
                $qb->andWhere("fv{$parsedFilterName}.value = :value{$parsedFilterName}");
                $qb->setParameter("value{$parsedFilterName}", $filterValue);
                break;

            case FieldFacet::FILE_TYPE:
                break;

            case FieldFacet::CHOICE_TYPE:
            case FieldFacet::CASCADE_TYPE:
            default:
                $qb->andWhere("UPPER(fv{$parsedFilterName}.value) LIKE :value{$parsedFilterName}");

                // a little of black magic because Doctrine Json type stores unicode seq for special chars
                $value = json_encode($filterValue);
                $value = trim($value, '"'); // removes string delimiters added by json encode

                $qb->setParameter("value{$parsedFilterName}", '%'.addslashes(strtoupper($value)).'%');
                break;
        }
    }
}
