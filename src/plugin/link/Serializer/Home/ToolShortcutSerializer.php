<?php

namespace Claroline\LinkBundle\Serializer\Home;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\LinkBundle\Entity\Home\ToolShortcutTab;

class ToolShortcutSerializer
{
    use SerializerTrait;

    public function getName()
    {
        return 'home_tool_shortcut_tab';
    }

    public function getClass()
    {
        return ToolShortcutTab::class;
    }

    public function serialize(ToolShortcutTab $tab): array
    {
        return [
            'tool' => $tab->getTool(),
        ];
    }

    public function deserialize(array $data, ToolShortcutTab $tab): ToolShortcutTab
    {
        $this->sipe('tool', 'setTool', $data, $tab);

        return $tab;
    }
}
