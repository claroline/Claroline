<?php

namespace Icap\BlogBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Icap\BlogBundle\Entity\Blog;

class MemberRepository extends EntityRepository
{
    public function getTrustedMembers(Blog $blog)
    {
        $query = $this->createQueryBuilder('m')
            ->select('m.id, author.firstName, author.lastName')
            ->innerJoin('m.user', 'author')
            ->andWhere('m.blog = :blogId')
            ->andWhere('m.trusted = :trusted')
            ->andWhere('m.banned = :banned')
            ->setParameter('blogId', $blog->getId())
            ->setParameter('trusted', true)
            ->setParameter('banned', false)
            ->orderBy('author.firstName, author.lastName', 'ASC');

        return $query->getQuery()->getResult();
    }

    public function getTrustedMember(Blog $blog, User $user)
    {
        $query = $this->createQueryBuilder('m')
            ->select('m')
            ->innerJoin('m.user', 'author')
            ->Where('m.blog = :blogId')
            ->andWhere('m.trusted = :trusted')
            ->andWhere('m.banned = :banned')
            ->andWhere('author.id = :userId')
            ->setParameter('blogId', $blog->getId())
            ->setParameter('trusted', true)
            ->setParameter('banned', false)
            ->setParameter('userId', $user->getId())
            ->orderBy('author.firstName, author.lastName', 'ASC');

        return $query->getQuery()->getResult();
    }

    public function getBannedMembers(Blog $blog)
    {
        $query = $this->createQueryBuilder('m')
            ->select(['m'])
            ->innerJoin('m.user', 'author')
            ->andWhere('m.blog = :blogId')
            ->andWhere('m.trusted = :trusted')
            ->andWhere('m.banned = :banned')
            ->setParameter('blogId', $blog->getId())
            ->setParameter('trusted', false)
            ->setParameter('banned', true)
            ->orderBy('author.firstName, author.lastName', 'ASC');

        return $query->getQuery()->getResult();
    }

    public function getBannedMember(Blog $blog, User $user)
    {
        $query = $this->createQueryBuilder('m')
            ->select(['m'])
            ->innerJoin('m.user', 'author')
            ->andWhere('m.blog = :blogId')
            ->andWhere('m.trusted = :trusted')
            ->andWhere('m.banned = :banned')
            ->andWhere('author.id = :userId')
            ->setParameter('blogId', $blog->getId())
            ->setParameter('trusted', false)
            ->setParameter('banned', true)
            ->setParameter('userId', $user->getId())
            ->orderBy('author.firstName, author.lastName', 'ASC');

        return $query->getQuery()->getResult();
    }
}
