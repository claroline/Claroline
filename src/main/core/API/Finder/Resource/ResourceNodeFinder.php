<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Finder\Resource;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ResourceNodeFinder extends AbstractFinder
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getClass(): string
    {
        return ResourceNode::class;
    }

    public function configureQueryBuilder(QueryBuilder $qb, array $searches = [], array $sortBy = null): QueryBuilder
    {
        $qb->join('obj.resourceType', 'ort');
        $qb->join('obj.workspace', 'ow');

        $parentJoin = false;

        foreach ($searches as $filterName => $filterValue) {
            switch ($filterName) {
                case 'meta.published':
                    $qb->andWhere('obj.published = :published');
                    $qb->setParameter('published', $filterValue);
                    break;

                case 'meta.uploadDestination': // TODO : remove me. this should not be managed here
                    $qb->andWhere("ort.name = 'directory'");
                    $qb->join(Directory::class, 'dir', 'WITH', 'dir.resourceNode = obj.id');
                    $qb->andWhere('dir.uploadDestination = true');
                    break;

                case 'meta.type': //should be the same as resourceType but something is wrong somewhere. It's an alias
                case 'resourceType':
                    if (is_array($filterValue)) {
                        $qb->andWhere('ort.name IN (:resourceType)');
                    } else {
                        $qb->andWhere('ort.name LIKE :resourceType');
                    }
                    $qb->setParameter('resourceType', $filterValue);
                    break;

                case 'resourceTypeBlacklist': // TODO : remove me. only used by obsolete dashboard query
                    if (is_array($filterValue)) {
                        $qb->andWhere("ort.name NOT IN (:{$filterName})");
                    }
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'resourceTypeEnabled':
                    $qb->andWhere("ort.isEnabled = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                    break;

                case 'workspace':
                    $qb->andWhere('ow.uuid = :workspaceUuid');
                    $qb->setParameter('workspaceUuid', $filterValue);
                    break;

                case 'path.after':
                    $qb->andWhere('UPPER(obj.path) != :path'); // required otherwise we also get the parent in the results
                    $qb->andWhere('UPPER(obj.path) LIKE :pathLike');
                    $qb->setParameter('path', strtoupper($filterValue));
                    $qb->setParameter('pathLike', strtoupper($filterValue).'%');
                    break;

                case 'parent':
                    if (is_null($filterValue)) {
                        $qb->andWhere('obj.parent IS NULL');
                    } else {
                        $qb->join('obj.parent', 'op');
                        $qb->andWhere('op.uuid = :parent');
                        $qb->setParameter('parent', $filterValue);
                        $parentJoin = true;
                    }
                    break;

                case 'roles':
                    $qb->leftJoin('obj.rights', 'rights');
                    $qb->join('rights.role', 'rightsr');
                    $qb->andWhere('rightsr.name IN (:roles)');
                    $qb->andWhere('BIT_AND(rights.mask, 1) = 1');
                    $qb->setParameter('roles', $filterValue);
                    break;

                default:
                    $this->setDefaults($qb, $filterName, $filterValue);
                    break;
            }
        }

        // if we don't explicitly set the parent, only grab resources from not archived workspaces
        if (!isset($searches['workspace']) && !isset($searches['parent'])) {
            $qb->andWhere('ow.archived = false');
        }

        if (!is_null($sortBy) && isset($sortBy['property']) && isset($sortBy['direction'])) {
            $sortByProperty = $sortBy['property'];
            $sortByDirection = 1 === $sortBy['direction'] ? 'ASC' : 'DESC';

            switch ($sortByProperty) {
                case 'meta.type':
                case 'resourceType':
                    $qb->orderBy('ort.name', $sortByDirection);
                    break;
                case 'meta.updated':
                    $qb->orderBy('obj.modificationDate', $sortByDirection);
                    break;
                case 'meta.published':
                    $qb->orderBy('obj.published', $sortByDirection);
                    break;
                case 'meta.created':
                    $qb->orderBy('obj.creationDate', $sortByDirection);
                    break;
                case 'workspace':
                    $qb->orderBy('ow.name', $sortByDirection);
                    break;
                case 'parent':
                    if (!$parentJoin) {
                        $qb->join('obj.parent', 'op');
                    }

                    $qb->orderBy('op.name', $sortByDirection);
                    break;
            }
        }

        return $qb;
    }

    protected function getExtraFieldMapping(): array
    {
        return [
            'meta.updated' => 'creationDate',
            'meta.created' => 'modificationDate',
            'meta.published' => 'published',
            'meta.views' => 'views',
        ];
    }
}
