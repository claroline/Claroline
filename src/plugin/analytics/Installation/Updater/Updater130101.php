<?php

namespace Claroline\AnalyticsBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\InstallationBundle\Updater\Updater;
use Psr\Log\LoggerInterface;

class Updater130101 extends Updater
{
    /** @var ObjectManager */
    private $om;

    public function __construct(
        ObjectManager $om,
        LoggerInterface $logger = null
    ) {
        $this->om = $om;
        $this->logger = $logger;
    }

    public function preUpdate()
    {
        $this->log('Remove old widget "progression"...');

        $widget = $this->om->getRepository(Widget::class)->findOneBy(['name' => 'progression']);
        if (!empty($widget)) {
            $this->om->remove($widget);
            $this->om->flush();
        }
    }
}
