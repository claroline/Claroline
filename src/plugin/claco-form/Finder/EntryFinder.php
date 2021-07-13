<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\LocationManager;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class EntryFinder extends AbstractFinder
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var LocationManager */
    private $locationManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var TranslatorInterface */
    private $translator;

    private $usedJoin = [];

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        LocationManager $locationManager,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator
    ) {
        $this->authorization = $authorization;
        $this->locationManager = $locationManager;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
    }

    public static function getClass(): string
    {
        return 'Claroline\ClacoFormBundle\Entity\Entry';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null, array $options = ['count' => false, 'page' => 0, 'limit' => -1])
    {
        $clacoFormRepo = $this->om->getRepository('ClarolineClacoFormBundle:ClacoForm');
        $fieldRepo = $this->om->getRepository('ClarolineClacoFormBundle:Field');

        // TODO : rights should not be checked here
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $isAnon = !$currentUser instanceof User;
        $clacoForm = null;
        $canEdit = false;
        $isCategoryManager = false;
        $searchEnabled = false;
        if (isset($searches['clacoForm'])) {
            $clacoForm = $clacoFormRepo->findOneById($searches['clacoForm']);
            if ($clacoForm) {
                $canEdit = $this->hasRight($clacoForm, 'EDIT');
                $isCategoryManager = !$isAnon && $this->isCategoryManager($clacoForm, $currentUser);
                $searchEnabled = $clacoForm->getSearchEnabled();
            }
        }

        $type = isset($searches['type']) ? $searches['type'] : null;

        if ($type) {
            switch ($type) {
                case 'all_entries':
                    $type = 'all';
                    break;
                case 'my_entries':
                    $type = 'my';
                    break;
                case 'manager_entries':
                    $type = 'manager';
                    break;
                default:
                    $type = null;
            }
        }
        if (is_null($type)) {
            if ($searchEnabled || $canEdit) {
                $type = 'all';
            } elseif (!$isAnon) {
                $type = $isCategoryManager ? 'manager' : 'my';
            }
        }
        if (is_null($type)) {
            return null;
        }
        switch ($type) {
            case 'all':
                if (!$canEdit) {
                    if ($isAnon) {
                        $qb->andWhere('obj.status = 1');
                    } elseif ($isCategoryManager) {
                        $qb->leftJoin('obj.user', 'u');
                        $qb->leftJoin('obj.categories', 'c');
                        $qb->leftJoin('c.managers', 'cm');
                        $searchEnabled ?
                            $qb->andWhere('obj.status = 1 OR u.id = :userId OR cm.id = :userId') :
                            $qb->andWhere('u.id = :userId OR cm.id = :userId');
                        $qb->setParameter('userId', $currentUser->getId());
                        $this->usedJoin['user'] = true;
                        $this->usedJoin['categories'] = true;
                    } else {
                        $qb->leftJoin('obj.user', 'u');
                        $searchEnabled ?
                            $qb->andWhere('obj.status = 1 OR u.id = :userId') :
                            $qb->andWhere('u.id = :userId');
                        $qb->setParameter('userId', $currentUser->getId());
                        $this->usedJoin['user'] = true;
                    }
                }
                break;
            case 'manager':
                $qb->join('obj.categories', 'c');
                $qb->join('c.managers', 'cm');
                $qb->andWhere('cm.id = :managerId');
                $qb->setParameter('managerId', $currentUser->getId());
                $this->usedJoin['categories'] = true;
                break;
            case 'my':
                if ($isAnon) {
                    $qb->leftJoin('obj.user', 'u');
                    $qb->andWhere('u.id = :userId');
                    $qb->setParameter('userId', -1);
                    $this->usedJoin['user'] = true;
                } else {
                    $qb->leftJoin('obj.user', 'u');
                    $qb->leftJoin('obj.entryUsers', 'eu');
                    $qb->leftJoin('eu.user', 'euu');
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->eq('u.id', ':userId'),
                        $qb->expr()->andX(
                            $qb->expr()->eq('euu.id', ':userId'),
                            $qb->expr()->eq('eu.shared', true)
                        )
                    ));
                    $qb->setParameter('userId', $currentUser->getId());
                    $this->usedJoin['user'] = true;
                }
                break;
        }
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'type':
                    break;
                case 'clacoForm':
                    $qb->join('obj.clacoForm', 'cf');
                    $qb->andWhere('cf.id = :clacoFormId');
                    $qb->setParameter('clacoFormId', $searches['clacoForm']);
                    break;
                case 'title':
                    $qb->andWhere('UPPER(obj.title) LIKE :title');
                    $qb->setParameter('title', '%'.strtoupper($filterValue).'%');
                    break;
                case 'status':
                    $qb->andWhere('obj.status IN (:status)');
                    $qb->setParameter('status', $filterValue ? [Entry::PUBLISHED] : [Entry::PENDING, Entry::UNPUBLISHED]);
                    break;
                case 'locked':
                    $qb->andWhere('obj.locked = :locked');
                    $qb->setParameter('locked', $filterValue);
                    break;
                case 'user':
                    if (!isset($this->usedJoin['user'])) {
                        $qb->join('obj.user', 'u');
                    }
                    $qb->andWhere("
                        UPPER(u.firstName) LIKE :name
                        OR UPPER(u.lastName) LIKE :name
                        OR UPPER(u.username) LIKE :name
                        OR CONCAT(UPPER(u.firstName), CONCAT(' ', UPPER(u.lastName))) LIKE :name
                        OR CONCAT(UPPER(u.lastName), CONCAT(' ', UPPER(u.firstName))) LIKE :name
                    ");
                    $qb->setParameter('name', '%'.strtoupper($filterValue).'%');
                    break;
                case 'createdAfter':
                    $qb->andWhere("obj.creationDate >= :{$filterName}");
                    $qb->setParameter($filterName, new \DateTime(date('Y-m-d', $filterValue)));
                    break;
                case 'createdBefore':
                    $qb->andWhere("obj.creationDate <= :{$filterName}");
                    $qb->setParameter($filterName, new \DateTime(date('Y-m-d', $filterValue)));
                    break;
                case 'categories':
                    if (!isset($this->usedJoin['categories'])) {
                        $qb->join('obj.categories', 'c');
                    }
                    $qb->andWhere('UPPER(c.name) LIKE :categoryName');
                    $qb->setParameter('categoryName', '%'.strtoupper($filterValue).'%');
                    break;
                case 'category':
                    if (!isset($this->usedJoin['categories'])) {
                        $qb->join('obj.categories', 'c');
                    }
                    $qb->andWhere('c.uuid = :categoryUuid');
                    $qb->setParameter('categoryUuid', $filterValue);
                    break;
                case 'keywords':
                    $qb->join('obj.keywords', 'k');
                    $qb->andWhere('UPPER(k.name) LIKE :keywordName');
                    $qb->setParameter('keywordName', '%'.strtoupper($filterValue).'%');
                    $this->usedJoin['keywords'] = true;
                    break;
                default:
                    $filterName = str_replace('values.', '', $filterName);
                    $field = $fieldRepo->findOneBy(['clacoForm' => $clacoForm, 'uuid' => $filterName]);
                    $this->filterField($qb, $filterName, $filterValue, $field);
            }
        }

        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'creationDate':
                case 'title':
                case 'user':
                case 'status':
                    $qb->orderBy("obj.{$sortByProperty}", $sortByDirection);
                    break;
                case 'categories':
                    if (!isset($this->usedJoin['categories'])) {
                        $qb->leftJoin('obj.categories', 'c');
                    }
                    $qb->orderBy('c.name', $sortByDirection);
                    break;
                case 'keywords':
                    if (!isset($this->usedJoin['keywords'])) {
                        $qb->leftJoin('obj.keywords', 'k');
                    }
                    $qb->orderBy('k.name', $sortByDirection);
                    break;
                default:
                    $field = $fieldRepo->findOneBy(['clacoForm' => $clacoForm, 'uuid' => $sortBy]);
                    $this->sortField($qb, $sortByProperty, $sortByDirection, $field);
            }
        }

        return $qb;
    }

    private function filterField(&$qb, $filterName, $filterValue, $field)
    {
        $parsedFilterName = str_replace('-', '', $filterName);

        if ($field) {
            $qb->join('obj.fieldValues', "fv{$parsedFilterName}");
            $qb->join("fv{$parsedFilterName}.field", "fvf{$parsedFilterName}");
            $qb->join("fv{$parsedFilterName}.fieldFacetValue", "fvffv{$parsedFilterName}");
            $qb->andWhere("fvf{$parsedFilterName}.uuid = :field{$parsedFilterName}");
            $qb->setParameter("field{$parsedFilterName}", $filterName);
            $this->usedJoin[$filterName] = true;

            switch ($field->getFieldFacet()->getType()) {
                case FieldFacet::NUMBER_TYPE:
                    $qb->andWhere("fvffv{$parsedFilterName}.floatValue = :value{$parsedFilterName}");
                    $qb->setParameter("value{$parsedFilterName}", $filterValue);
                    break;
                case FieldFacet::DATE_TYPE:
                case FieldFacet::FILE_TYPE:
                    break;
                case FieldFacet::CHOICE_TYPE:
                    $options = $field->getDetails();
                    $multiple = isset($options['multiple']) && $options['multiple'];

                    if ($multiple) {
                        $qb->andWhere("UPPER(fvffv{$parsedFilterName}.arrayValue) LIKE :value{$parsedFilterName}");
                    } else {
                        $qb->andWhere("UPPER(fvffv{$parsedFilterName}.stringValue) LIKE :value{$parsedFilterName}");
                    }

                    // a little of black magic because Doctrine Json type stores unicode seq for special chars
                    $value = json_encode($filterValue);
                    $value = trim($value, '"'); // removes string delimiters added by json encode

                    $qb->setParameter("value{$parsedFilterName}", '%'.addslashes(strtoupper($value)).'%');
                    break;
                case FieldFacet::CASCADE_TYPE:
                    $qb->andWhere("UPPER(fvffv{$parsedFilterName}.arrayValue) LIKE :value{$parsedFilterName}");

                    // a little of black magic because Doctrine Json type stores unicode seq for special chars
                    $value = json_encode($filterValue);
                    $value = trim($value, '"'); // removes string delimiters added by json encode

                    $qb->setParameter("value{$parsedFilterName}", '%'.addslashes(strtoupper($value)).'%');
                    break;
                case FieldFacet::BOOLEAN_TYPE:
                    $qb->andWhere("fvffv{$parsedFilterName}.boolValue = :value{$parsedFilterName}");
                    $qb->setParameter("value{$parsedFilterName}", $filterValue);
                    break;
                default:
                    $qb->andWhere("UPPER(fvffv{$parsedFilterName}.stringValue) LIKE :value{$parsedFilterName}");
                    $qb->setParameter("value{$parsedFilterName}", '%'.strtoupper($filterValue).'%');
            }
        }
    }

    private function sortField(&$qb, $sortBy, $direction, $field)
    {
        $parsedSortBy = str_replace('-', '', $sortBy);

        if ($field) {
            if (!isset($this->usedJoin[$sortBy])) {
                $qb->leftJoin('obj.fieldValues', "fv{$parsedSortBy}");
                $qb->leftJoin("fv{$parsedSortBy}.field", "fvf{$parsedSortBy}");
                $qb->leftJoin("fv{$parsedSortBy}.fieldFacetValue", "fvffv{$parsedSortBy}");
                $qb->andWhere("fvf{$parsedSortBy}.uuid = :field{$parsedSortBy} OR fvf{$parsedSortBy} = :nullValue");
                $qb->setParameter("field{$parsedSortBy}", $sortBy);
                $qb->setParameter('nullValue', null);
            }

            switch ($field->getFieldFacet()->getType()) {
                case FieldFacet::NUMBER_TYPE:
                    $qb->orderBy("fvffv{$parsedSortBy}.floatValue", $direction);
                    break;
                case FieldFacet::DATE_TYPE:
                    $qb->orderBy("fvffv{$parsedSortBy}.dateValue", $direction);
                    break;
                case FieldFacet::CHOICE_TYPE:
                    $options = $field->getDetails();

                    if (isset($options['multiple']) && $options['multiple']) {
                        $qb->orderBy("fvffv{$parsedSortBy}.arrayValue", $direction);
                    } else {
                        $qb->orderBy("fvffv{$parsedSortBy}.stringValue", $direction);
                    }
                    break;
                case FieldFacet::CASCADE_TYPE:
                    $qb->orderBy("fvffv{$parsedSortBy}.arrayValue", $direction);
                    break;
                default:
                    $qb->orderBy("fvffv{$parsedSortBy}.stringValue", $direction);
            }
        }
    }

    private function hasRight(ClacoForm $clacoForm, $right)
    {
        $collection = new ResourceCollection([$clacoForm->getResourceNode()]);

        return $this->authorization->isGranted($right, $collection);
    }

    private function isCategoryManager(ClacoForm $clacoForm, User $user)
    {
        $categories = $clacoForm->getCategories();

        foreach ($categories as $category) {
            $managers = $category->getManagers();

            foreach ($managers as $manager) {
                if ($manager->getId() === $user->getId()) {
                    return true;
                }
            }
        }

        return false;
    }
}
