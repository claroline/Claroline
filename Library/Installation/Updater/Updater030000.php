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

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\AdminTool;


class Updater030000
{
    private $container;
    private $logger;
    private $oldCachePath;
    private $newCachePath;
    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct($container)
    {
        $this->container     = $container;
        $this->objectManager = $container->get('claroline.persistence.object_manager');
        $ds = DIRECTORY_SEPARATOR;
        $this->oldCachePath = $container
                ->getParameter('kernel.root_dir') . $ds . 'cache' . $ds . 'claroline.cache.php';
        $this->newCachePath = $container
                ->getParameter('kernel.root_dir') . $ds . 'cache' . $ds . 'claroline.cache.ini';
    }

    public function postUpdate()
    {
        $this->log('Updating cache...');
        $this->container->get('claroline.manager.cache_manager')->refresh();
        $this->log('Removing old cache...');

        if (file_exists($this->oldCachePath)) {
            unlink($this->oldCachePath);
        }

        $this->log('Creating admin referential competence tools...');

        $tools = array(
            array('competence_referencial', 'icon-reorder'),
        );

        $existingTool = $this->objectManager->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findByName('competence_referencial');

        if (count($existingTool) === 0) {
            foreach ($tools as $tool) {
                $entity = new AdminTool();
                $entity->setName($tool[0]);
                $entity->setClass($tool[1]);
                $this->objectManager->persist($entity);
            }
        }

        $this->objectManager->flush();
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
