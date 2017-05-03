<?php

namespace Icap\BibliographyBundle\Repository;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class BookReferenceRepository extends EntityRepository
{
    public function findAllByWorkspace(Workspace $workspace, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('bookReference')
            ->select(['bookReference', 'resourceNode'])
            ->join('bookReference.resourceNode', 'resourceNode')
            ->where('resourceNode.workspace= :workspace')
            ->andWhere('resourceNode.published= :published')
            ->addOrderBy('bookReference.author')
            ->addOrderBy('resourceNode.name')
            ->setParameter('workspace', $workspace)
            ->setParameter('published', 1)
            ->getQuery();

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findOneByIsbnAndByWorkspace($isbn, Workspace $workspace, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('bookReference')
            ->select(['bookReference', 'resourceNode'])
            ->join('bookReference.resourceNode', 'resourceNode')
            ->where('bookReference.isbn= :isbn')
            ->andWhere('resourceNode.workspace= :workspace')
            ->setParameter('isbn', $isbn)
            ->setParameter('workspace', $workspace)
            ->getQuery();

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }
}
