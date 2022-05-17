<?php

namespace Claroline\LinkBundle\Entity\Home;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\HomeBundle\Entity\Type\AbstractTab;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_home_tab_tool_shortcut")
 */
class ToolShortcutTab extends AbstractTab
{
    use Id;

    /**
     * @ORM\Column(nullable=false)
     *
     * @var string
     */
    private $tool;

    public static function getType(): string
    {
        return 'tool_shortcut';
    }

    public function getTool(): ?string
    {
        return $this->tool;
    }

    public function setTool(string $tool)
    {
        $this->tool = $tool;
    }
}
