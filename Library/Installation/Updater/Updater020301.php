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

class Updater020301
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
        $this->log('Update agenda...');
        $agenda = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName('agenda');
        $agenda->setIsConfigurableInWorkspace(false);
        $agenda->setIsConfigurableInDesktop(false);
        $em->flush();
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
