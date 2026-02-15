<?php

namespace Claroline\InstallationBundle\Updater\Helper;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

trait RemoveResourceTrait
{
    private function removeResource(string $resourceName): void
    {
        if (!$this->om instanceof ObjectManager) {
            throw new \RuntimeException(sprintf('RemoveResourceTrait requires the ObjectManager (@%s to be injected in your service.', ObjectManager::class));
        }

        $resourceType = $this->om->getRepository(ResourceType::class)->findOneBy([
            'name' => $resourceName,
        ]);

        if ($resourceType) {
            $this->om->remove($resourceType);
            $this->om->flush();
        }
    }
}
