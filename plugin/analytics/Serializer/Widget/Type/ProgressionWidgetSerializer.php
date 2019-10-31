<?php

namespace Claroline\AnalyticsBundle\Serializer\Widget\Type;

use Claroline\AnalyticsBundle\Entity\Widget\Type\ProgressionWidget;
use Claroline\AppBundle\API\Serializer\SerializerTrait;

class ProgressionWidgetSerializer
{
    use SerializerTrait;

    public function getClass()
    {
        return ProgressionWidget::class;
    }

    public function getName()
    {
        return 'progression_widget';
    }

    public function serialize(ProgressionWidget $widget, array $options = []): array
    {
        return [
            'levelMax' => $widget->getLevelMax(),
        ];
    }

    public function deserialize($data, ProgressionWidget $widget, array $options = []): ProgressionWidget
    {
        $this->sipe('levelMax', 'setlevelMax', $data, $widget);

        return $widget;
    }
}
