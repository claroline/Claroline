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
use JMS\Serializer\Annotation as JMS;

class ResourceIconItemFilenameList
{
    /**
     * Icons refering to resource types.
     *
     * @var array
     */
    private $resourceIcons = [];

    /**
     * Icons refering to file types.
     *
     * @var array
     */
    private $fileIcons = [];

    /**
     * Icons all icons regardless their reference to resource of files.
     *
     * @var array
     * @JMS\Groups({"details"})
     */
    private $allIcons = [];

    /**
     * Array of all mimeTypes present in the list.
     *
     * @var array
     * @JMS\Groups({"details"})
     */
    private $mimeTypes = [];

    /**
     * Array of all icons in the list by original mimeType.
     *
     * @JMS\Groups({"details"})
     */
    private $icons = [];

    public function __construct($icons = null)
    {
        $this->addIcons($icons);
    }

    public function addIcons($icons)
    {
        if (!empty($icons)) {
            foreach ($icons as $icon) {
                $this->addIcon($icon);
            }
        }
    }

    public function addIcon(IconItem $icon)
    {
        $mimeType = $icon->getMimeType();
        //Otherwise put it in resourceIcons or fileIcons array and also in allIcons array
        $this->mimeTypes[] = $mimeType;
        $this->icons[$mimeType] = $icon;
        // Check if is resource icon
        if (strpos($mimeType, 'custom/') !== false) {
            // For every resoruce, give option for a different icon
            $filename = str_replace('custom/', '', $mimeType);
            $this->resourceIcons[$filename] = $this->allIcons[$filename] = new ResourceIconItemFilename(
                $filename,
                $icon->getRelativeUrl(),
                [$mimeType]
            );

            return;
        }

        // For all filetypes represented by the same icon, give only one option
        $filename = pathinfo($icon->getRelativeUrl(), PATHINFO_FILENAME);
        if (array_key_exists($filename, $this->fileIcons)) {
            $this->fileIcons[$filename]->addMimeType($mimeType);
        } else {
            $this->fileIcons[$filename] = $this->allIcons[$filename] = new ResourceIconItemFilename(
                $filename,
                $icon->getRelativeUrl(),
                [$mimeType]
            );
        }
    }

    /**
     * @return array
     */
    public function getResourceIcons()
    {
        return $this->resourceIcons;
    }

    /**
     * @return array
     */
    public function getFileIcons()
    {
        return $this->fileIcons;
    }

    /**
     * @return array
     */
    public function getAllIcons()
    {
        return $this->allIcons;
    }

    /**
     * @return array
     */
    public function getMimeTypes()
    {
        return $this->mimeTypes;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->allIcons);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function isInList($key)
    {
        return array_key_exists($key, $this->allIcons);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function isInResourceIcons($key)
    {
        return array_key_exists($key, $this->resourceIcons);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function isInFileIcons($key)
    {
        return array_key_exists($key, $this->fileIcons);
    }

    /**
     * @param $key
     *
     * @return ResourceIconItemFilename | null
     */
    public function getItemByKey($key)
    {
        if ($this->isInList($key)) {
            return $this->allIcons[$key];
        }

        return null;
    }

    /**
     * @param $mimeType
     *
     * @return IconItem | null
     */
    public function getIconByMimeType($mimeType)
    {
        return array_key_exists($mimeType, $this->icons) ? $this->icons[$mimeType] : null;
    }

    public function prependShortcutIcon(IconItem $icon)
    {
        array_unshift(
            $this->resourceIcons,
            new ResourceIconItemFilename($icon->getName(), $icon->getRelativeUrl(), ['shortcut'])
        );
    }
}
