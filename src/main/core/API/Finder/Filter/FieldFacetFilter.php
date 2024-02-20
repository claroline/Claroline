<?php

namespace Claroline\CoreBundle\API\Finder\Filter;

use Claroline\AppBundle\API\Finder\FinderFilterInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class FieldFacetFilter implements FinderFilterInterface
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function addFilter(QueryBuilder $qb, string $alias, ?array $searches = []): QueryBuilder
    {
        $filterName = $searches['field'] ?? null;
        $filterValue = $searches['value'] ?? null;

        if (empty($filterName)) {
            return $qb;
        }

        $field = $this->om->getRepository(FieldFacet::class)->findOneBy(['uuid' => $filterName]);
        if (empty($field)) {
            return $qb;
        }
        $parsedFilterName = str_replace('-', '', $filterName);

        $qb->leftJoin("{$alias}.facetValues", "fv{$parsedFilterName}");
        $qb->leftJoin("fv{$parsedFilterName}.fieldFacet", "ff{$parsedFilterName}");
        $qb->andWhere("ff{$parsedFilterName}.uuid = :field{$parsedFilterName}");
        $qb->setParameter("field{$parsedFilterName}", $filterName);

        switch ($field->getType()) {
            case FieldFacet::DATE_TYPE:
            case FieldFacet::BOOLEAN_TYPE:
            case FieldFacet::NUMBER_TYPE:
                $qb->andWhere("fv{$parsedFilterName}.value = :value{$parsedFilterName}");
                $qb->setParameter("value{$parsedFilterName}", $filterValue);
                break;

            case FieldFacet::FILE_TYPE:
                break;

            case FieldFacet::CASCADE_TYPE:
                $qb->andWhere("UPPER(fvffv{$parsedFilterName}.value) LIKE :value{$parsedFilterName}");

                $value = is_array($filterValue) ? end($filterValue) : $filterValue;
                // a little of black magic because Doctrine Json type stores unicode seq for special chars
                $value = json_encode($value);
                $value = trim($value, '"'); // removes string delimiters added by json encode

                $qb->setParameter("value{$parsedFilterName}", '%'.addslashes(strtoupper($value)).'%');
                break;

            case FieldFacet::CHOICE_TYPE:
            default:
                $qb->andWhere("UPPER(fv{$parsedFilterName}.value) LIKE :value{$parsedFilterName}");

                // a little of black magic because Doctrine Json type stores unicode seq for special chars
                $value = json_encode($filterValue);
                $value = trim($value, '"'); // removes string delimiters added by json encode

                $qb->setParameter("value{$parsedFilterName}", '%'.addslashes(strtoupper($value)).'%');
                break;
        }

        return $qb;
    }

    public function addSort(QueryBuilder $qb, string $alias, string $sortBy, string $direction): QueryBuilder
    {
        $field = $this->om->getRepository(FieldFacet::class)->findOneBy(['uuid' => $sortBy]);
        if (empty($field)) {
            return $qb;
        }

        $parsedSortBy = str_replace('-', '', $sortBy);

        if (!in_array("fv{$parsedSortBy}", $qb->getAllAliases())) {
            $qb->leftJoin("{$alias}.fieldValues", "fv{$parsedSortBy}");
            $qb->leftJoin("fv{$parsedSortBy}.field", "fvf{$parsedSortBy}");
            $qb->leftJoin("fvf{$parsedSortBy}.fieldFacet", "ff{$parsedSortBy}", Join::WITH, "ff{$parsedSortBy}.uuid = :field{$parsedSortBy}");
            $qb->leftJoin("fv{$parsedSortBy}.fieldFacetValue", "fvffv{$parsedSortBy}");
            $qb->setParameter("field{$parsedSortBy}", $sortBy);
        }

        $qb->orderBy("fvffv{$parsedSortBy}.value", $direction);

        return $qb;
    }
}
