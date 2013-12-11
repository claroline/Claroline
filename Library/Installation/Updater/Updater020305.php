<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Widget\Widget;

class Updater020304
{
    private $container;
    private $logger;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $widget = new Widget();
        $widget->setName('agenda');
        $widget->setConfigurable(false);
        $widget->setIcon('fake/icon/path');
        $widget->setPlugin(null);
        $widget->setExportable(false);
        $widget->setDisplayableInDesktop(true);
        $widget->setDisplayableInWorkspace(true);
        $em->persist($widget);
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }
}

