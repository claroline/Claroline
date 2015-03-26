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

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Tool\PwsToolConfig;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\Resource\PwsRightsManagementAccess;
use Claroline\InstallationBundle\Updater\Updater;

class Updater040500 extends Updater
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
        $this->updateOrder();
    }

    private function updateOrder()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $dirType = $entityManager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findByName('directory');
        $nodes = $entityManager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
            ->createQueryBuilder('node')
            ->select('node.id')
            ->where('node.resourceType = :directorytype')
            ->setParameter('directorytype', $dirType)
            ->getQuery()->getResult();

        /** @var \Claroline\CoreBundle\Manager\ResourceManager $resourceManager */
        $resourceManager = $this->container->get('claroline.manager.resource_manager');


        $nbNodes = 0;
        $currentBatchNodes = 0;
        $totalNodes = count($nodes);
        $refreshOutputInterval = (int)round($totalNodes / 50);
        if (0 === $refreshOutputInterval) {
            $refreshOutputInterval = 1;
        }

        $this->log(sprintf('Updating %d resource order - %s', $totalNodes, date('Y/m/d H:i:s')));
        $this->log('It may take a while to process, go grab a coffee.');

        foreach ($nodes as $node) {
            $resourceManager->reorder($node['id']);
            $nbNodes++;
            $currentBatchNodes++;

            if ($refreshOutputInterval === $currentBatchNodes) {
                $this->log('    ' . $nbNodes . ' resource ordered - ' . date('Y/m/d H:i:s') . ' - ' . $this->convert(memory_get_usage(true)));
                $currentBatchNodes = 0;
            }
        }
        $this->log('Resource order updated.');
    }

    public function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
}
