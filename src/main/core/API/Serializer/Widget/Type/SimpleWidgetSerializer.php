<?php

namespace Claroline\CoreBundle\API\Serializer\Widget\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Widget\Type\SimpleWidget;
use Claroline\CoreBundle\Manager\Template\PlaceholderManager;

class SimpleWidgetSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly PlaceholderManager $placeholderManager
    ) {
    }

    public function getClass(): string
    {
        return SimpleWidget::class;
    }

    public function getName(): string
    {
        return 'simple_widget';
    }

    public function serialize(SimpleWidget $widget, array $options = []): array
    {
        return [
            'contentRaw' => $widget->getContent(),
            'content' => $this->placeholderManager->replacePlaceholders($widget->getContent() ?? ''),
            'placeholders' => $this->placeholderManager->getAvailablePlaceholders(),
        ];
    }

    public function deserialize($data, SimpleWidget $widget, array $options = []): SimpleWidget
    {
        $this->sipe('contentRaw', 'setContent', $data, $widget);

        return $widget;
    }
}
