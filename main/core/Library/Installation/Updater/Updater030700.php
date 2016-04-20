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

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater030700 extends Updater
{
    private $container;
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updatePublishTypes();
        $this->removeOldWebFiles();
    }

    private function updatePublishTypes()
    {
        $this->log('Updating publish types...');
        $types = $this->om->getRepository('ClarolineCoreBundle:Home\Type')->findAll();
        foreach ($types as $type) {
            $type->setPublish(true);
            $this->om->persist($type);
        }

        $this->om->flush();
    }

    private function removeOldWebFiles()
    {
        $this->log('Removing old web files...');
        $webDir = $this->container->getParameter('claroline.param.web_dir');

        $toRemove = array(
            '/maintenance.html.php',
            '/upgrade/download_log.php',
            '/upgrade/upgrade.html.php',
        );

        foreach ($toRemove as $file) {
            $this->log('Removing '.$webDir.$file.'...');
            @unlink($webDir.$file);
        }
    }
}
