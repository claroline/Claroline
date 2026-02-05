<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\InstallationBundle\Updater\Updater;

class Updater150013 extends Updater
{
    public function __construct(
        private readonly ObjectManager $om,
    ) {
    }

    public function postUpdate(): void
    {
        $this->migrateFileRights();
        $this->migrateUrlRights();
    }

    private function migrateFileRights(): void
    {
        /** @var ResourceRights[] $fileCreationRights */
        $fileCreationRights = $this->om->getRepository(ResourceRights::class)
            ->createQueryBuilder('r')
            ->where('r.creatableTypes LIKE :type')
            ->andWhere('r.creatableTypes LIKE :start')
            ->setParameter('type', '%file%')
            ->setParameter('start', '{%')
            ->getQuery()
            ->getResult();

        foreach ($fileCreationRights as $i => $fileCreationRight) {
            $fileCreationRight->setCreatableResourceTypes(array_merge(array_values($fileCreationRight->getCreatableResourceTypes()), [
                'pdf', 'video', 'image', 'audio',
            ]));

            $this->om->persist($fileCreationRight);

            if (0 === $i % 200) {
                $this->om->flush();
            }
        }

        $this->om->flush();
    }

    private function migrateUrlRights(): void
    {
        /** @var ResourceRights[] $urlCreationRights */
        $urlCreationRights = $this->om->getRepository(ResourceRights::class)
            ->createQueryBuilder('r')
            ->where('r.creatableTypes LIKE :type')
            ->andWhere('(r.creatableTypes LIKE :start OR r.creatableTypes NOT LIKE :youtubeType)')
            ->setParameter('type', '%hevinci_url%')
            ->setParameter('start', '{%')
            ->setParameter('youtubeType', '%youtube_video%')
            ->getQuery()
            ->getResult();

        foreach ($urlCreationRights as $i => $urlCreationRight) {
            $urlCreationRight->setCreatableResourceTypes(array_merge(array_values($urlCreationRight->getCreatableResourceTypes()), [
                'youtube_video',
            ]));

            $this->om->persist($urlCreationRight);

            if (0 === $i % 200) {
                $this->om->flush();
            }
        }

        $this->om->flush();
    }
}
