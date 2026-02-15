<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Tool;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_tool_mask_decoder')]
#[ORM\UniqueConstraint(name: 'tool_mask_decoder_unique_tool_and_name', columns: ['tool_name', 'name'])]
#[ORM\Entity()]
class ToolMaskDecoder
{
    use Id;

    public const DEFAULT_ACTIONS = ['open', 'follow', 'edit', 'administrate'];
    public const DEFAULT_VALUES = [
        'open' => 1,
        'edit' => 2,
        'administrate' => 4,
        'follow' => 8,
    ];

    #[ORM\Column(type: Types::INTEGER)]
    private int $value;

    #[ORM\Column]
    private string $name;

    #[ORM\Column(name: 'tool_name', nullable: false)]
    private string $tool;

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getTool(): string
    {
        return $this->tool;
    }

    public function setTool(string $tool): void
    {
        $this->tool = $tool;
    }
}
