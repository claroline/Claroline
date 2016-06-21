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
use Claroline\InstallationBundle\Updater\Updater;
use Claroline\CoreBundle\Persistence\ObjectManager;

class Updater040500 extends Updater
{
    /** @var ContainerInterface  */
    private $container;

    /** @var ObjectManager */
    private $objectManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->objectManager = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateOrder();
    }

    private function updateOrder()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        /** @var \Claroline\CoreBundle\Repository\ResourceNodeRepository $resourceNodeRepository */
        $resourceNodeRepository = $entityManager->getRepository('ClarolineCoreBundle:Resource\ResourceNode');

        /** @var \Claroline\CoreBundle\Manager\ResourceManager $resourceManager */
        $resourceManager = $this->container->get('claroline.manager.resource_manager');

        /** @var \Claroline\CoreBundle\Entity\Resource\ResourceType $dirType */
        $dirType = $entityManager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findByName('directory');

        $baseResourceNodeQuery = $resourceNodeRepository
            ->createQueryBuilder('resourceNode')
            ->where('resourceNode.resourceType = :resourceNodeType')
            ->setParameter('resourceNodeType', $dirType);

        $countResourceNodeQuery = clone $baseResourceNodeQuery;

        $totalNodes = $countResourceNodeQuery
            ->select('COUNT(resourceNode.id)')
            ->getQuery()->getSingleScalarResult();

        $nbNodes = 0;
        $currentBatchNodes = 0;
        $refreshOutputInterval = (int) round($totalNodes / 50);
        if (0 === $refreshOutputInterval) {
            $refreshOutputInterval = 1;
        }

        $this->log(sprintf('Updating %d resource order - %s', $totalNodes, date('Y/m/d H:i:s')));
        $this->log('It may take a while to process, go grab a coffee.');

        $nodesQuery = $baseResourceNodeQuery->getQuery();

        $iterableResult = $nodesQuery->iterate();
        foreach ($iterableResult as $row) {
            $node = $row[0];
            $resourceManager->reorder($node, true);

            ++$nbNodes;
            ++$currentBatchNodes;

            if ($refreshOutputInterval === $currentBatchNodes) {
                $this->log('    '.$nbNodes.' resource ordered - '.date('Y/m/d H:i:s').' - '.$this->convert(memory_get_usage(true)));
                $currentBatchNodes = 0;
                $this->objectManager->clear();
            }
        }

        if (0 < $currentBatchNodes) {
            $this->log('    '.$nbNodes.' resource ordered - '.date('Y/m/d H:i:s').' - '.$this->convert(memory_get_usage(true)));
        }

        $this->log('Resource order updated.');
    }

    public function convert($size)
    {
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');

        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2).' '.$unit[$i];
    }
}
