<?php

namespace Claroline\CoreBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130600 extends Updater
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function preUpdate()
    {
        $this->removePlugin('Icap', 'SocialmediaBundle');
        $this->removePlugin('Claroline', 'PlannedNotificationBundle');
        $this->removePlugin('Claroline', 'BookingBundle');
        $this->removePlugin('UJM', 'LtiBundle');

        $this->om->flush();
    }

    private function removePlugin(string $vendorName, string $bundleName)
    {
        $this->log(sprintf('Remove %s plugin...', $vendorName.$bundleName));

        $plugin = $this->om->getRepository(Plugin::class)->findOneBy([
            'vendorName' => $vendorName,
            'bundleName' => $bundleName,
        ]);

        if ($plugin) {
            $this->om->remove($plugin);
        }
    }
}
