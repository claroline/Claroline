<?php

namespace Claroline\CursusBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Widget\Type\ListWidget;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130100 extends Updater
{
    /** @var ObjectManager */
    private $om;

    public function __construct(
        ObjectManager $om
    ) {
        $this->om = $om;
    }

    /**
     * Replace `public_course_sessions` DataSource (has been removed) in widgets by `course_sessions`.
     * PreUpdate event is mandatory to migrate data before the DataSource is removed.
     */
    public function preUpdate()
    {
        // retrieve old DataSource
        $publicSource = $this->om->getRepository(DataSource::class)->findOneBy([
            'name' => 'public_course_sessions',
        ]);

        if (!$publicSource) {
            return;
        }

        // retrieve new DataSource
        $allSource = $this->om->getRepository(DataSource::class)->findOneBy([
            'name' => 'course_sessions',
        ]);

        // retrieve all widgets using this DataSource
        $widgets = $this->om->getRepository(WidgetInstance::class)->findBy([
            'dataSource' => $publicSource,
        ]);

        /** @var WidgetInstance $widget */
        foreach ($widgets as $widget) {
            // replace DataSource
            $widget->setDataSource($allSource);
            $this->om->persist($widget);

            // add public filter to the widget
            $listParameters = $this->om
                ->getRepository(ListWidget::class)
                ->findOneBy(['widgetInstance' => $widget]);

            if ($listParameters) {
                $filters = $listParameters->getFilters();

                $filters[] = [
                    'property' => 'publicRegistration',
                    'value' => true,
                ];

                $listParameters->setFilters($filters);

                $this->om->persist($listParameters);
            }
        }

        $this->om->flush();
    }
}
