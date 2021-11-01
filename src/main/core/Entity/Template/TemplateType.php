<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Template;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\CoreBundle\Entity\Plugin;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_template_type",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="template_unique_type", columns={"entity_name"})
 *     }
 * )
 */
class TemplateType
{
    use Id;
    use Name;
    use Uuid;

    /**
     * @ORM\Column(name="entity_type", type="string")
     *
     * @var string
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Plugin")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Plugin
     */
    private $plugin;

    /**
     * @ORM\Column(type="json", nullable=true)
     *
     * @var array
     */
    private $placeholders = [];

    /**
     * @ORM\Column(name="default_template", nullable=true)
     *
     * @var string
     */
    private $defaultTemplate;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getPlugin(): ?Plugin
    {
        return $this->plugin;
    }

    public function setPlugin(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    public function setPlaceholders(array $placeholders = [])
    {
        $this->placeholders = $placeholders;
    }

    public function getDefaultTemplate(): ?string
    {
        return $this->defaultTemplate;
    }

    public function setDefaultTemplate(?string $defaultTemplate)
    {
        $this->defaultTemplate = $defaultTemplate;
    }
}
