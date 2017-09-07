<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Theme;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Theme.
 *
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Theme\ThemeRepository")
 * @ORM\Table(name="claro_theme")
 */
class Theme
{
    use UuidTrait;

    /**
     * Unique identifier of the theme.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * Name of the theme.
     *
     * @ORM\Column()
     *
     * @var string
     */
    private $name;

    /**
     * Small description for the theme.
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $description = null;

    /**
     * Is the theme enabled (aka. can be selected as current theme) ?
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * Is it the default platform theme ?
     *
     * @ORM\Column(name="is_default", type="boolean")
     *
     * @var bool
     */
    private $default = false;

    /**
     * The plugin to which the theme belongs, if any.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Plugin")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Plugin
     */
    private $plugin;

    /**
     * The user who owns the theme, if any.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    /**
     * If true, the default theme will be included in the templates too.
     *
     * Notice :
     *   This is kept for retro-compatibility purpose.
     *   LESS themes should directly include the default theme.
     *
     * @ORM\Column(name="extending_default", type="boolean")
     *
     * @var bool
     */
    private $extendingDefault = false;

    /**
     * Theme constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Theme constructor.
     */
    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set enabled.
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Is enabled ?
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set default.
     *
     * @param bool $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * Is default ?
     *
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
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
     * Get plugin.
     *
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return bool
     */
    public function isExtendingDefault()
    {
        return $this->extendingDefault;
    }

    /**
     * @param bool $extendingDefault
     */
    public function setExtendingDefault($extendingDefault)
    {
        $this->extendingDefault = $extendingDefault;
    }

    /**
     * @return bool
     */
    public function isCustom()
    {
        return empty($this->plugin);
    }

    /**
     * Returns a lowercase version of the theme name, with spaces replaced
     * by hyphens (used as an id or a file/directory name).
     *
     * @return string
     */
    public function getNormalizedName()
    {
        return str_replace(' ', '-', strtolower($this->getName()));
    }
}
