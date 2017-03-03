<?php

/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 1/25/17
 */

namespace Claroline\CoreBundle\Library\Icon;

use Claroline\CoreBundle\Entity\Icon\IconItem;

class ResourceIconSetIconItemList
{
    /**
     * Icons defined in set.
     *
     * @var ResourceIconItemFilenameList
     */
    private $setIcons;

    /**
     * Default icons (not defined in set).
     *
     * @var ResourceIconItemFilenameList
     */
    private $defaultIcons;

    public function __construct($setIcons = null, $defaultIcons = null)
    {
        $this->setIcons = new ResourceIconItemFilenameList($setIcons);
        $this->defaultIcons = new ResourceIconItemFilenameList($defaultIcons);
    }

    public function addSetIcons(array $icons)
    {
        $this->setIcons->addIcons($icons);
    }

    public function addSetIcon(IconItem $icon)
    {
        $this->setIcons->addIcon($icon);
    }

    public function addDefaultIcons(array $icons)
    {
        $this->defaultIcons->addIcons($icons);
    }

    public function addDefaultIcon(IconItem $icon)
    {
        $this->defaultIcons->addIcon($icon);
    }

    /**
     * @return ResourceIconItemFilenameList
     */
    public function getSetIcons()
    {
        return $this->setIcons;
    }

    /**
     * @return ResourceIconItemFilenameList
     */
    public function getDefaultIcons()
    {
        return $this->defaultIcons;
    }

    public function isInSetIcons($key)
    {
        return $this->setIcons->isInList($key);
    }

    public function isInDefaultIcons($key)
    {
        return $this->defaultIcons->isInList($key);
    }

    public function isInList($key)
    {
        return $this->isInSetIcons($key) || $this->isInDefaultIcons($key);
    }

    public function getFromSetIconsByKey($key)
    {
        return $this->setIcons->getItemByKey($key);
    }

    public function getFromDefaultIconsByKey($key)
    {
        return $this->defaultIcons->getItemByKey($key);
    }

    public function prependShortcutIcon(IconItem $icon, $isDefault = false)
    {
        if ($isDefault) {
            $this->defaultIcons->prependShortcutIcon($icon);
        } else {
            $this->setIcons->prependShortcutIcon($icon);
        }
    }
}
