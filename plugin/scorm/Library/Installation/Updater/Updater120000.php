<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120000 extends Updater
{
    private $container;
    protected $logger;
    private $om;

    public function __construct(ContainerInterface $container, $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->deactivateOldResourceTypes();
    }

    private function deactivateOldResourceTypes()
    {
        $resourceTypeRepo = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $scorm12Type = $resourceTypeRepo->findOneBy(['name' => 'claroline_scorm_12']);
        $scorm2004Type = $resourceTypeRepo->findOneBy(['name' => 'claroline_scorm_2004']);

        if (!empty($scorm12Type) || !empty($scorm2004Type)) {
            $this->log('Deactivating Scorm 1.2 & Scorm 2004...');

            if (!empty($scorm12Type)) {
                $scorm12Type->setEnabled(false);
                $this->om->persist($scorm12Type);
            }
            if (!empty($scorm2004Type)) {
                $scorm2004Type->setEnabled(false);
                $this->om->persist($scorm2004Type);
            }

            $this->om->flush();
            $this->log('Scorm 1.2 & Scorm 2004 are deactivated.');
        }
    }
}
