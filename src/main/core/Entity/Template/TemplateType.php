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
use Claroline\CoreBundle\Entity\Plugin;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_template_type",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="template_unique_type",
 *             columns={"type_name"}
 *         )
 *     }
 * )
 */
class TemplateType
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column(name="type_name")
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Plugin")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Plugin
     */
    private $plugin;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @var array
     */
    private $placeholders;

    /**
     * @ORM\Column(name="default_template", nullable=true)
     *
     * @var string
     */
    private $defaultTemplate;

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get plugin.
     *
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * Set plugin.
     *
     * @param Plugin $plugin
     */
    public function setPlugin(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Get placeholders.
     *
     * @return array
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * Set placeholders.
     *
     * @param array|null $placeholders
     */
    public function setPlaceholders(array $placeholders = null)
    {
        $this->placeholders = $placeholders;
    }

    /**
     * Get default template.
     *
     * @return string
     */
    public function getDefaultTemplate()
    {
        return $this->defaultTemplate;
    }

    /**
     * Set default template.
     *
     * @param string $defaultTemplate
     */
    public function setDefaultTemplate($defaultTemplate)
    {
        $this->defaultTemplate = $defaultTemplate;
    }
}
