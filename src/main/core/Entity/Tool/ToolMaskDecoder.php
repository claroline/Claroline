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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(
 *     name="claro_tool_mask_decoder",
 *     uniqueConstraints={
 *
 *         @ORM\UniqueConstraint(
 *             name="tool_mask_decoder_unique_tool_and_name",
 *             columns={"tool_name", "name"}
 *         )
 *     })
 */
class ToolMaskDecoder
{
    use Id;

    public const DEFAULT_ACTIONS = ['open', 'edit', 'administrate'];
    public const DEFAULT_VALUES = [
        'open' => 1,
        'edit' => 2,
        'administrate' => 4,
    ];

    /**
     * @ORM\Column(type="integer")
     */
    private int $value;

    /**
     * @ORM\Column()
     */
    protected string $name;

    /**
     * @ORM\Column(name="tool_name", nullable=false)
     */
    protected string $tool;

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
