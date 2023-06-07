<?php

namespace Claroline\InstallationBundle\Updater\Helper;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Plugin;

trait RemovePluginTrait
{
    private function removePlugin(string $vendorName, string $bundleName): void
    {
        if (!$this->om instanceof ObjectManager) {
            throw new \RuntimeException(sprintf('RemovePluginTrait requires the ObjectManager (@%s to be injected in your service.', ObjectManager::class));
        }

        $plugin = $this->om->getRepository(Plugin::class)->findOneBy([
            'vendorName' => $vendorName,
            'bundleName' => $bundleName,
        ]);

        if ($plugin) {
            $this->om->remove($plugin);
        }
    }
}
