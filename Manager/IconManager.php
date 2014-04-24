<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Repository\ResourceIconRepository;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Library\Utilities\ThumbnailCreator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.icon_manager")
 */
class IconManager
{
    /** @var ThumbnailCreator */
    private $creator;
    /** @var ResourceIconRepository */
    private $repo;
    /** @var string */
    private $fileDir;
    /** @var string */
    private $thumbDir;
    /** @var string */
    private $rootDir;
    /** @var ClaroUtilities */
    private $ut;
    /** @var ObjectManager */
    private $om;

    /**
     * @DI\InjectParams({
     *     "creator"  = @DI\Inject("claroline.utilities.thumbnail_creator"),
     *     "fileDir"  = @DI\Inject("%claroline.param.files_directory%"),
     *     "thumbDir" = @DI\Inject("%claroline.param.thumbnails_directory%"),
     *     "rootDir"  = @DI\Inject("%kernel.root_dir%"),
     *     "ut"       = @DI\Inject("claroline.utilities.misc"),
     *     "om"       = @DI\Inject("claroline.persistence.object_manager"),
     * })
     */
    public function __construct(
        ThumbnailCreator $creator,
        $fileDir,
        $thumbDir,
        $rootDir,
        ClaroUtilities $ut,
        ObjectManager $om
    )
    {
        $this->creator = $creator;
        $this->repo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon');
        $this->fileDir = $fileDir;
        $this->thumbDir = $thumbDir;
        $this->rootDir = $rootDir;
        $this->ut = $ut;
        $this->om = $om;
    }

    /**
     * Create (if possible) and|or returns an icon for a resource
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     */
    public function getIcon(AbstractResource $resource)
    {
        $node = $resource->getResourceNode();
        $mimeElements = explode('/', $node->getMimeType());
        $ds = DIRECTORY_SEPARATOR;
        // if video or img => generate the thumbnail, otherwise find an existing one.
        if (($mimeElements[0] === 'video' || $mimeElements[0] === 'image')) {
            $this->om->startFlushSuite();
            $thumbnailPath = $this->createFromFile($this->fileDir. $ds . $resource->getHashName(), $mimeElements[0]);

            if ($thumbnailPath !== null) {
                $thumbnailName = pathinfo($thumbnailPath, PATHINFO_BASENAME);
                $relativeUrl = "thumbnails/{$thumbnailName}";
                $icon = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
                $icon->setMimeType('custom');
                $icon->setRelativeUrl($relativeUrl);
                $icon->setShortcut(false);
                $this->om->persist($icon);
                $this->createShortcutIcon($icon);
                $this->om->endFlushSuite();

                return $icon;
            }
            $this->om->endFlushSuite();
        }

        //default & fallback
        return $this->searchIcon($node->getMimeType());
    }

    /**
     * Return the icon of a specified mimeType.
     * The most specific icon for the mime type will be returned.
     *
     * @param string $mimeType
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     */
    public function searchIcon($mimeType)
    {
        $mimeElements = explode('/', $mimeType);

        $icon = $this->repo->findOneByMimeType($mimeType);

        if ($icon === null) {
            $icon = $this->repo->findOneByMimeType($mimeElements[0]);

            if ($icon === null) {
                $icon = $this->repo->findOneByMimeType('custom/default');
            }
        }

        return $icon;
    }

    /**
     * Creates the shortcut icon for an existing icon.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceIcon $icon
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     *
     * @throws \RuntimeException
     */
    public function createShortcutIcon(ResourceIcon $icon)
    {
        $this->om->startFlushSuite();

        $ds = DIRECTORY_SEPARATOR;

        try {
            $originalIconLocation = "{$this->rootDir}{$ds}..{$ds}web{$ds}{$icon->getRelativeUrl()}";
            $shortcutLocation = $this->creator->shortcutThumbnail($originalIconLocation);
        } catch (\Exception $e) {
            $shortcutLocation = "{$this->rootDir}{$ds}.."
            . "{$ds}web{$ds}bundles{$ds}clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}shortcut-default.png";
        }

        $shortcutIcon = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceIcon');

        if (strstr($shortcutLocation, "bundles")) {
            $tmpRelativeUrl = strstr($shortcutLocation, "bundles");
        } else {
            $tmpRelativeUrl = strstr($shortcutLocation, "thumbnails");
        }

        $relativeUrl = str_replace('\\', '/', $tmpRelativeUrl);
        $shortcutIcon->setRelativeUrl($relativeUrl);
        $shortcutIcon->setMimeType($icon->getMimeType());
        $shortcutIcon->setShortcut(true);
        $icon->setShortcutIcon($shortcutIcon);
        $shortcutIcon->setShortcutIcon($shortcutIcon);
        $this->om->persist($icon);
        $this->om->persist($shortcutIcon);

        $this->om->endFlushSuite();

        return $shortcutIcon;

    }

    /**
     * Creates a custom ResourceIcon entity from a File (wich should contain an image).
     * (for instance if the thumbnail of a resource is changed)
     *
     * @param UploadedFile $file
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     */
    public function createCustomIcon(UploadedFile $file)
    {
        $this->om->startFlushSuite();
        $iconName = $file->getClientOriginalName();
        $extension = pathinfo($iconName, PATHINFO_EXTENSION);
        $hashName = $this->ut->generateGuid() . "." . $extension;
        $file->move($this->thumbDir, $hashName);
        //entity creation
        $icon = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $icon->setRelativeUrl("thumbnails/{$hashName}");
        $icon->setMimeType('custom');
        $icon->setShortcut(false);
        $this->om->persist($icon);
        $this->createShortcutIcon($icon);
        $this->om->endFlushSuite();

        return $icon;
    }

    /**
     * Creates an image from a file.
     *
     * @param string $filePath
     * @param string $baseMime (image|video)
     *
     * @return null|string $thumnnailPath
     */
    public function createFromFile($filePath, $baseMime)
    {
        $ds = DIRECTORY_SEPARATOR;
        $newPath = $this->thumbDir. $ds . $this->ut->generateGuid() . ".png";

        $thumbnailPath = null;
        if ($baseMime === 'video') {
            try {
                $thumbnailPath = $this->creator->fromVideo($filePath, $newPath, 100, 100);
            } catch (\Exception $e) {
                $thumbnailPath = null;
                //error handling ? $thumbnailPath = null
            }
        }

        if ($baseMime === 'image') {
            try {
                $thumbnailPath = $this->creator->fromImage($filePath, $newPath, 100, 100);
            } catch (\Exception $e) {
                $thumbnailPath = null;
                //error handling ? $thumbnailPath = null
            }
        }

        return $thumbnailPath;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceIcon $icon
     */
    public function delete(ResourceIcon $icon)
    {
        if ($icon->getMimeType() === 'custom') {
            //search if this icon is used elsewhere (ie copy)
            $res = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findBy(array('icon' => $icon));

            if (count($res) <= 1 && $icon->isShortcut() === false) {
                $shortcut = $icon->getShortcutIcon();
                $this->om->remove($shortcut);
                $this->om->remove($icon);
                $this->om->flush();
                $this->removeImageFromThumbDir($icon);
                $this->removeImageFromThumbDir($icon->getShortcutIcon());
            }
        }
    }

    /**
     * Replace a node icon.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $resource
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceIcon $icon
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function replace(ResourceNode $resource, ResourceIcon $icon)
    {
        $this->om->startFlushSuite();
        $oldIcon = $resource->getIcon();
        $this->delete($oldIcon);
        $resource->setIcon($icon);
        $this->om->persist($resource);
        $this->om->endFlushSuite();

        return $resource;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceIcon $icon
     */
    public function removeImageFromThumbDir(ResourceIcon $icon)
    {
        if (preg_match('#^thumbnails#', $icon->getRelativeUrl())) {
            $pathName = $this->rootDir . '/../web/' . $icon->getRelativeUrl();

            if (file_exists($pathName)) {
                unlink($pathName);
            }
        }
    }
}
