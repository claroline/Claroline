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

use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater030109
{
    private $em;
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    public function postUpdate()
    {
        $this->createBadgeUsageWidget();
    }

    private function createBadgeUsageWidget()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        try {
            $widgetKey = 'badge_usage';

            $workspaceWidget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')->findOneByName($widgetKey);

            if (is_null($workspaceWidget)) {
                $this->log('Creating workspace badge widget...');
                $widget = new Widget();
                $widget->setName($widgetKey);
                $widget->setConfigurable(false);
                $widget->setIcon('fake/icon/path');
                $widget->setPlugin(null);
                $widget->setExportable(false);
                $widget->setDisplayableInDesktop(false);
                $widget->setDisplayableInWorkspace(true);
                $em->persist($widget);
                $em->flush();
            }
        } catch (MappingException $e) {
            $this->log('A MappingException has been thrown while trying to get Widget repository');
        }
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
