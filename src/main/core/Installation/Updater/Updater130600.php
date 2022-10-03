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

    public function postUpdate()
    {
        $this->log('Remove SocialMedia plugin...');

        $socialMedia = $this->om->getRepository(Plugin::class)->findOneBy([
            'vendorName' => 'Icap',
            'bundleName' => 'SocialmediaBundle',
        ]);

        if ($socialMedia) {
            $this->om->remove($socialMedia);
            $this->om->flush();
        }
    }
}
