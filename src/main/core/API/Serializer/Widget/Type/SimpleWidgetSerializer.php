<?php

namespace Claroline\CoreBundle\API\Serializer\Widget\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Widget\Type\SimpleWidget;

class SimpleWidgetSerializer
{
    use SerializerTrait;

    public function getClass()
    {
        return SimpleWidget::class;
    }

    public function getName()
    {
        return 'simple_widget';
    }

    public function serialize(SimpleWidget $widget, array $options = []): array
    {
        return [
            'content' => $widget->getContent(),
        ];
    }

    public function deserialize($data, SimpleWidget $widget, array $options = []): SimpleWidget
    {
        $this->sipe('content', 'setContent', $data, $widget);

        return $widget;
    }
}
