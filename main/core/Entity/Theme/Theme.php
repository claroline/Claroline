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

use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Manager\ThemeManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_theme")
 */
class Theme
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Plugin")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $plugin;

    /**
     * @ORM\Column(name="extending_default", type="boolean")
     */
    protected $extendingDefault = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Plugin $plugin
     */
    public function setPlugin(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
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
        // TODO: use a dedicated db field to store that information

        return !in_array($this->name, ThemeManager::listStockThemeNames());
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
