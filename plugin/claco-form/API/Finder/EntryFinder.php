<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\API\Finder;

use Claroline\AppBundle\API\FinderInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\ClacoForm;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\Organization\LocationManager;
use Doctrine\ORM\QueryBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.api.finder.clacoform.entry")
 * @DI\Tag("claroline.finder")
 */
class EntryFinder implements FinderInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var LocationManager */
    private $locationManager;

    /** @var ObjectManager */
    private $om;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var TranslatorInterface */
    private $translator;

    private $clacoFormRepo;
    private $fieldRepo;

    private $usedJoin = [];

    /**
     * EntryFinder constructor.
     *
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "locationManager" = @DI\Inject("claroline.manager.organization.location_manager"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "translator"      = @DI\Inject("translator")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param LocationManager               $locationManager
     * @param ObjectManager                 $om
     * @param TokenStorageInterface         $tokenStorage
     * @param TranslatorInterface           $translator
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        LocationManager $locationManager,
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator
    ) {
        $this->authorization = $authorization;
        $this->locationManager = $locationManager;
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->clacoFormRepo = $om->getRepository('ClarolineClacoFormBundle:ClacoForm');
        $this->fieldRepo = $om->getRepository('ClarolineClacoFormBundle:Field');
    }

    public function getClass()
    {
        return 'Claroline\ClacoFormBundle\Entity\Entry';
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $isAnon = 'anon.' === $currentUser;
        $clacoForm = $this->clacoFormRepo->findOneById($searches['clacoForm']);
        $canEdit = $this->hasRight($clacoForm, 'EDIT');
        $isCategoryManager = !$isAnon && $this->isCategoryManager($clacoForm, $currentUser);
        $searchEnabled = $clacoForm->getSearchEnabled();

        $qb->join('obj.clacoForm', 'cf');
        $qb->andWhere('cf.id = :clacoFormId');
        $qb->setParameter('clacoFormId', $searches['clacoForm']);

        $type = isset($searches['type']) ? $searches['type'] : null;

        if ($type) {
            switch ($type) {
                case $this->translator->trans('all_entries', [], 'clacoform'):
                    $type = 'all';
                    break;
                case $this->translator->trans('my_entries', [], 'clacoform'):
                    $type = 'my';
                    break;
                case $this->translator->trans('manager_entries', [], 'clacoform'):
                    $type = 'manager';
                    break;
                default:
                    $type = null;
            }
        }
        if (is_null($type)) {
            if ($searchEnabled || $this->hasRight($clacoForm, 'EDIT')) {
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
                $qb->leftJoin('obj.user', 'u');
                $qb->leftJoin('obj.entryUsers', 'eu');
                $qb->leftJoin('eu.user', 'euu');
                $qb->andWhere('u.id = :userId');
                $qb->orWhere('(euu.id = :userId AND eu.shared = true)');
                $qb->setParameter('userId', $currentUser->getId());
                $this->usedJoin['user'] = true;
                break;
        }
        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'clacoForm':
                    break;
                case 'title':
                    $qb->andWhere('UPPER(obj.title) LIKE :title');
                    $qb->setParameter('title', '%'.strtoupper($filterValue).'%');
                    break;
                case 'status':
                    $qb->andWhere('obj.status IN (:status)');
                    $qb->setParameter('status', $filterValue ? [1] : [0, 2]);
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
                case 'keywords':
                    $qb->join('obj.keywords', 'k');
                    $qb->andWhere('UPPER(k.name) LIKE :keywordName');
                    $qb->setParameter('keywordName', '%'.strtoupper($filterValue).'%');
                    $this->usedJoin['keywords'] = true;
                    break;
                default:
                    $field = $this->fieldRepo->findOneBy(['clacoForm' => $clacoForm, 'id' => $filterName]);
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
                    $field = $this->fieldRepo->findOneBy(['clacoForm' => $clacoForm, 'id' => $sortBy]);
                    $this->sortField($qb, $sortByProperty, $sortByDirection, $field);
            }
        }

        return $qb;
    }

    private function filterField(&$qb, $filterName, $filterValue, $field)
    {
        if ($field) {
            $qb->join('obj.fieldValues', "fv{$filterName}");
            $qb->join("fv{$filterName}.field", "fvf{$filterName}");
            $qb->join("fv{$filterName}.fieldFacetValue", "fvffv{$filterName}");
            $qb->andWhere("fvf{$filterName}.id = :field{$filterName}");
            $qb->setParameter("field{$filterName}", $filterName);
            $this->usedJoin[$filterName] = true;

            switch ($field->getFieldFacet()->getType()) {
                case FieldFacet::FLOAT_TYPE:
                    $qb->andWhere("fvffv{$filterName}.floatValue = :value{$filterName}");
                    $qb->setParameter("value{$filterName}", $filterValue);
                    break;
                case FieldFacet::DATE_TYPE:
                    break;
                case FieldFacet::COUNTRY_TYPE:
                    $countries = $this->locationManager->getCountries();
                    $pattern = "/$filterValue/i";
                    $keys = [];

                    foreach ($countries as $key => $country) {
                        if (preg_match($pattern, $country)) {
                            $keys[] = $key;
                        }
                    }
                    $qb->andWhere("fvffv{$filterName}.stringValue IN (:value{$filterName})");
                    $qb->setParameter("value{$filterName}", $keys);
                    break;
                case FieldFacet::CHECKBOXES_TYPE:
                case FieldFacet::CASCADE_SELECT_TYPE:
                    $qb->andWhere("UPPER(fvffv{$filterName}.arrayValue) LIKE :value{$filterName}");
                    $qb->setParameter("value{$filterName}", '%'.strtoupper($filterValue).'%');
                    break;
                default:
                    $qb->andWhere("UPPER(fvffv{$filterName}.stringValue) LIKE :value{$filterName}");
                    $qb->setParameter("value{$filterName}", '%'.strtoupper($filterValue).'%');
            }
        }
    }

    private function sortField(&$qb, $sortBy, $direction, $field)
    {
        if ($field) {
            if (!isset($this->usedJoin[$sortBy])) {
                $qb->leftJoin('obj.fieldValues', "fv{$sortBy}");
                $qb->leftJoin("fv{$sortBy}.field", "fvf{$sortBy}");
                $qb->leftJoin("fv{$sortBy}.fieldFacetValue", "fvffv{$sortBy}");
                $qb->andWhere("fvf{$sortBy}.id = :field{$sortBy} OR fvf{$sortBy} = :nullValue");
                $qb->setParameter("field{$sortBy}", $sortBy);
                $qb->setParameter('nullValue', null);
            }

            switch ($field->getFieldFacet()->getType()) {
                case FieldFacet::FLOAT_TYPE:
                    $qb->orderBy("fvffv{$sortBy}.floatValue", $direction);
                    break;
                case FieldFacet::DATE_TYPE:
                    $qb->orderBy("fvffv{$sortBy}.dateValue", $direction);
                    break;
                case FieldFacet::CHECKBOXES_TYPE:
                case FieldFacet::CASCADE_SELECT_TYPE:
                    $qb->orderBy("fvffv{$sortBy}.arrayValue", $direction);
                    break;
                default:
                    $qb->orderBy("fvffv{$sortBy}.stringValue", $direction);
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
