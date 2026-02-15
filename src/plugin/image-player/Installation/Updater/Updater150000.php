<?php

namespace Claroline\ImagePlayerBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\ImagePlayerBundle\Component\Resource\ImageResource;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\DBAL\Connection;

class Updater150000 extends Updater
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ObjectManager $om
    ) {
    }

    public function postUpdate(): void
    {
        $videoType = $this->om->getRepository(ResourceType::class)->findOneBy([
            'name' => ImageResource::getName(),
        ]);

        $updateResources = $this->connection->prepare(
            'UPDATE claro_resource_node SET resource_type_id = :resourceType WHERE mime_type LIKE "image%"'
        );
        $updateResources->bindValue('resourceType', $videoType->getId());
        $updateResources->executeQuery();
    }
}
