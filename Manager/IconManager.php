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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Repository\ResourceIconRepository;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Library\Utilities\ThumbnailCreator;
use Symfony\Component\HttpFoundation\File\File;
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
    /** @var string */
    private $basepath;

    /**
     * @DI\InjectParams({
     *     "creator"  = @DI\Inject("claroline.utilities.thumbnail_creator"),
     *     "fileDir"  = @DI\Inject("%claroline.param.files_directory%"),
     *     "thumbDir" = @DI\Inject("%claroline.param.thumbnails_directory%"),
     *     "rootDir"  = @DI\Inject("%kernel.root_dir%"),
     *     "ut"       = @DI\Inject("claroline.utilities.misc"),
     *     "om"       = @DI\Inject("claroline.persistence.object_manager"),
     *     "basepath" = @DI\Inject("%claroline.param.relative_thumbnail_base_path%")
     * })
     */
    public function __construct(
        ThumbnailCreator $creator,
        $fileDir,
        $thumbDir,
        $rootDir,
        ClaroUtilities $ut,
        ObjectManager $om,
        $basepath
    )
    {
        $this->creator = $creator;
        $this->repo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon');
        $this->fileDir = $fileDir;
        $this->thumbDir = $thumbDir;
        $this->rootDir = $rootDir;
        $this->ut = $ut;
        $this->om = $om;
        $this->basepath = $basepath;
    }

    /**
     * Create (if possible) and|or returns an icon for a resource
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     */
    public function getIcon(AbstractResource $resource, Workspace $workspace = null)
    {
        $node = $resource->getResourceNode();
        $mimeElements = explode('/', $node->getMimeType());
        $ds = DIRECTORY_SEPARATOR;
        // if video or img => generate the thumbnail, otherwise find an existing one.
        if (($mimeElements[0] === 'video' || $mimeElements[0] === 'image')) {
            $this->om->startFlushSuite();
            $thumbnailPath = $this->createFromFile(
                $this->fileDir. $ds . $resource->getHashName(),
                $mimeElements[0],
                $workspace
            );

            if ($thumbnailPath !== null) {
                $thumbnailName = pathinfo($thumbnailPath, PATHINFO_BASENAME);

                if (is_null($workspace)) {
                    $relativeUrl = $this->basepath . "/{$thumbnailName}";
                } else {
                    $relativeUrl = $this->basepath .
                        $ds .
                        $workspace->getCode() .
                        $ds .
                        $thumbnailName;
                }
                $icon = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
                $icon->setMimeType('custom');
                $icon->setRelativeUrl($relativeUrl);
                $icon->setShortcut(false);
                $this->om->persist($icon);
                $this->createShortcutIcon($icon, $workspace);
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
    public function createShortcutIcon(ResourceIcon $icon, Workspace $workspace = null)
    {
        $this->om->startFlushSuite();

        $ds = DIRECTORY_SEPARATOR;

        try {
            $originalIconLocation = "{$this->rootDir}{$ds}..{$ds}web{$ds}{$icon->getRelativeUrl()}";
            $shortcutLocation = $this->creator->shortcutThumbnail($originalIconLocation, $workspace);
        } catch (\Exception $e) {
            $shortcutLocation = "{$this->rootDir}{$ds}.."
            . "{$ds}web{$ds}bundles{$ds}clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}shortcut-default.png";
        }

        $shortcutIcon = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceIcon');

        if (strstr($shortcutLocation, "bundles")) {
            $tmpRelativeUrl = strstr($shortcutLocation, "bundles");
        } else {
            $tmpRelativeUrl = strstr($shortcutLocation, $this->basepath);
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
     * @param File $file
     * @param Workspace $workspace (for the storage directory...)
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     */
    public function createCustomIcon(File $file, Workspace $workspace = null)
    {
        $this->om->startFlushSuite();
        $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

        if (is_null($workspace)) {
            $dest = $this->thumbDir;
            $hashName = $this->ut->generateGuid() . "." . $extension;
        } else {
            $dest = $this->thumbDir . DIRECTORY_SEPARATOR . $workspace->getCode();
            $hashName = $workspace->getCode() .
                DIRECTORY_SEPARATOR .
                $this->ut->generateGuid() .
                "." .
                $extension;
        }
        $file->move($dest, $hashName);
        //entity creation
        $icon = $this->om->factory('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $icon->setRelativeUrl("$this->basepath/{$hashName}");
        $icon->setMimeType('custom');
        $icon->setShortcut(false);
        $this->om->persist($icon);
        $this->createShortcutIcon($icon, $workspace);
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
    public function createFromFile($filePath, $baseMime, Workspace $workspace = null)
    {
        $ds = DIRECTORY_SEPARATOR;

        if (is_null($workspace)) {
            $prefix = $this->thumbDir;
        } else {
            $prefix = $this->thumbDir . $ds . $workspace->getCode();

            if (!is_dir($prefix)) {
                @mkdir($prefix);
            }
        }
        $newPath = $prefix . $ds . $this->ut->generateGuid() . ".png";

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
    public function delete(ResourceIcon $icon, Workspace $workspace = null)
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
                $this->removeImageFromThumbDir($icon, $workspace);
                $this->removeImageFromThumbDir($icon->getShortcutIcon(), $workspace);
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

        if (!$oldIcon->isShortcut()) {
            $oldShortcutIcon = $oldIcon->getShortcutIcon();
            $shortcutIcon = $icon->getShortcutIcon();

            if (!is_null($oldShortcutIcon) && !is_null($shortcutIcon)) {
                $nodes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                    ->findBy(array('icon' => $oldShortcutIcon));

                foreach ($nodes as $node) {
                    $node->setIcon($shortcutIcon);
                    $this->om->persist($node);
                }
            }
        }
        $this->delete($oldIcon);
        $resource->setIcon($icon);
        $this->om->persist($resource);
        $this->om->endFlushSuite();

        return $resource;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceIcon $icon
     */
    public function removeImageFromThumbDir(ResourceIcon $icon, Workspace $workspace = null)
    {
        if (preg_match('#^thumbnails#', $icon->getRelativeUrl())) {
            $pathName = $this->rootDir . '/../web/' . $icon->getRelativeUrl();

            if (file_exists($pathName)) {
                unlink($pathName);

                if (!is_null($workspace)) {
                    $dir = $this->thumbDir . DIRECTORY_SEPARATOR . $workspace->getCode();

                    if (is_dir($dir) && $this->isDirectoryEmpty($dir)) {
                        rmdir($dir);
                    }
                }
            }
        }
    }

    public function getDefaultIconMap()
    {
        return array(
            array('res_default.png', 'custom/default'),
            array('res_activity.png', 'custom/activity'),
            array('res_file.png', 'custom/file'),
            array('res_folder.png', 'custom/directory'),
            array('res_text.png', 'text/plain'),
            array('res_text.png', 'custom/text'),
            array('res_url.png', 'custom/url'),
            array('res_exercice.png', 'custom/exercice'),
            array('res_audio.png', 'audio'),
            array('res_video.png', 'video'),
            array('res_msexcel.png', 'application/excel'),
            array('res_msexcel.png', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            array('res_mspowerpoint.png', 'application/powerpoint'),
            array('res_mspowerpoint.png', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
            array('res_msword.png', 'application/msword'),
            array('res_msword.png', 'application/vnd.oasis.opendocument.text'),
            array('res_msword.png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            array('res_pdf.png', 'application/pdf'),
            array('res_image.png', 'image'),
            array('res_vector.png', 'application/postscript'),
            array('res_vector.png', 'application/ai'),
            array('res_vector.png', 'application/illustrator'),
            array('res_vector.png', 'image/svg+xml'),
            array('res_zip.png', 'application/zip'),
            array('res_zip.png', 'application/x-rar-compressed'),
            array('res_archive.png', 'application/x-gtar'),
            array('res_archive.png', 'application/x-7z-compressed')
        );
    }

    private function isDirectoryEmpty($dirName)
    {
        $files = array ();
        $dirHandle = opendir($dirName);

        if ($dirHandle) {

            while ($file = readdir($dirHandle)) {

                if ($file !== '.' && $file !== '..') {
                    $files[] = $file;
                    break;
                }
            }
            closedir($dirHandle);
        }

        return count($files) === 0;
    }
}
