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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Widget\Type\ListWidget;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120547 extends Updater
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->om = $container->get(ObjectManager::class);
    }

    public function postUpdate()
    {
        $this->migrateResourceWidget();
    }

    private function migrateResourceWidget()
    {
        $listWidget = $this->om->getRepository(Widget::class)->findOneBy(['name' => 'list']);
        $resourceSource = $this->om->getRepository(DataSource::class)->findOneBy(['name' => 'resources']);

        $widgetInstances = $this->om->getRepository(WidgetInstance::class)->findBy([
            'widget' => $listWidget,
            'dataSource' => $resourceSource,
        ]);

        $this->log(sprintf('Found %d resources list widgets to migrate...', count($widgetInstances)));
        foreach ($widgetInstances as $widgetInstance) {
            /** @var ListWidget $widget */
            $widget = $this->om->getRepository(ListWidget::class)->findOneBy([
                'widgetInstance' => $widgetInstance,
            ]);

            if ($widget && !empty($widget->getFilters())) {
                $filters = $widget->getFilters();
                foreach ($filters as $index => $value) {
                    if (isset($value['property']) && 'parent' === $value['property'] && is_numeric($value['value'])) {
                        /** @var ResourceNode $node */
                        $node = $this->om->getRepository(ResourceNode::class)->find($value['value']);

                        // update value
                        $filters[$index]['value'] = $node->getUuid();
                        $widget->setFilters($filters);

                        $this->om->persist($widget);
                        break;
                    }
                }
            }
        }

        $this->om->flush();
    }
}
