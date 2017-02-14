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

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Library\Utilities\ThumbnailCreator;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\ResourceIconRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @DI\Service("claroline.manager.icon_manager")
 */
class IconManager
{
    use LoggableTrait;

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
    ) {
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
     * Create (if possible) and|or returns an icon for a resource.
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
                $this->fileDir.$ds.$resource->getHashName(),
                $mimeElements[0],
                $workspace
            );

            if ($thumbnailPath !== null) {
                $thumbnailName = pathinfo($thumbnailPath, PATHINFO_BASENAME);

                if (is_null($workspace)) {
                    $relativeUrl = $this->basepath."/{$thumbnailName}";
                } else {
                    $relativeUrl = $this->basepath.
                        $ds.
                        $workspace->getCode().
                        $ds.
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

        $relativeUrl = $this->createShortcutFromRelativeUrl($icon->getRelativeUrl(), $workspace);
        $shortcutIcon = new ResourceIcon();
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
     * (for instance if the thumbnail of a resource is changed).
     *
     * @param File      $file
     * @param Workspace $workspace (for the storage directory...)
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     */
    public function createCustomIcon(File $file, Workspace $workspace = null)
    {
        $fileName = $file instanceof UploadedFile ? $file->getClientOriginalName() : $file->getFilename();
        $this->om->startFlushSuite();
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (is_null($workspace)) {
            $dest = $this->thumbDir;
            $hashName = $this->ut->generateGuid().'.'.$extension;
        } else {
            $dest = $this->thumbDir.DIRECTORY_SEPARATOR.$workspace->getCode();
            $hashName = $workspace->getCode().
                DIRECTORY_SEPARATOR.
                $this->ut->generateGuid().
                '.'.
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
     * @param string    $filePath
     * @param string    $baseMime  (image|video)
     * @param Workspace $workspace
     *
     * @return null|string
     */
    public function createFromFile($filePath, $baseMime, Workspace $workspace = null)
    {
        $ds = DIRECTORY_SEPARATOR;

        if (is_null($workspace)) {
            $prefix = $this->thumbDir;
        } else {
            $prefix = $this->thumbDir.$ds.$workspace->getCode();

            if (!is_dir($prefix)) {
                @mkdir($prefix);
            }
        }
        $newPath = $prefix.$ds.$this->ut->generateGuid().'.png';

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
                ->findBy(['icon' => $icon]);

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
                    ->findBy(['icon' => $oldShortcutIcon]);

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
            $pathName = $this->rootDir.'/../web/'.$icon->getRelativeUrl();

            if (file_exists($pathName)) {
                unlink($pathName);

                if (!is_null($workspace)) {
                    $dir = $this->thumbDir.DIRECTORY_SEPARATOR.$workspace->getCode();

                    if (is_dir($dir) && $this->isDirectoryEmpty($dir)) {
                        rmdir($dir);
                    }
                }
            }
        }
    }

    public function refresh(ResourceIcon $icon)
    {
        $shortcut = $icon->getShortcutIcon();
        $newUrl = $this->createShortcutFromRelativeUrl($icon->getRelativeUrl());
        $shortcut->setRelativeUrl($newUrl);
        $this->om->persist($shortcut);
        $this->om->flush();
    }

    public function getDefaultIconMap()
    {
        return [
            ['res_default.png', 'custom/default'],
            ['res_activity.png', 'custom/activity'],
            ['res_file.png', 'custom/file'],
            ['res_folder.png', 'custom/directory'],
            ['res_text.png', 'text/plain'],
            ['res_text.png', 'custom/text'],

            //array('res_url.png', 'custom/url'),
            //array('res_exercice.png', 'custom/exercice'),
            ['res_jpeg.png', 'image'],
            ['res_audio.png', 'audio'],
            ['res_avi.png', 'video'],

            //images
            ['res_bmp.png', 'image/bmp'],
            ['res_bmp.png', 'image/x-windows-bmp'],
            ['res_jpeg.png', 'image/jpeg'],
            ['res_jpeg.png', 'image/pjpeg'],
            ['res_gif.png', 'image/gif'],
            ['res_tiff.png', 'image/tiff'],
            ['res_tiff.png', 'image/x-tiff'],

            //videos
            ['res_mp4.png', 'video/mp4'],
            ['res_mpeg.png', 'video/mpeg'],
            ['res_mpeg.png', 'audio/mpeg'],

            //sounds
            ['res_wav.png', 'audio/wav'],
            ['res_wav.png', 'audio/x-wav'],

            ['res_mp3.png', 'audio/mpeg3'],
            ['res_mp3.png', 'audio/x-mpeg3'],
            ['res_mp3.png', 'audio/mp3'],
            ['res_mp3.png', 'audio/mpeg'],

            //html
            ['res_html.png', 'text/html'],

            //xls
            ['res_xls.png', 'application/excel'],
            ['res_xls.png', 'application/vnd.ms-excel'],
            ['res_xls.png', 'application/msexcel'],
            ['res_xls.png', 'application/x-msexcel'],
            ['res_xls.png', 'application/x-ms-excel'],
            ['res_xls.png', 'application/x-excel'],
            ['res_xls.png', 'application/xls'],
            ['res_xls.png', 'application/x-xls'],
            ['res_xls.png', 'application/x-dos_ms_excel'],

            //xlsx
            ['res_xlsx.png', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],

            //odt
            ['res_odt.png', 'application/vnd.oasis.opendocument.text '],

            //ppt
            ['res_ppt.png', 'application/mspowerpoint'],
            ['res_ppt.png', 'application/powerpoint'],
            ['res_ppt.png', 'application/vnd.ms-powerpoint'],
            ['res_ppt.png', 'application/application/x-mspowerpoint'],

            //pptx
            ['res_pptx.png', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],

            //doc
            ['res_doc.png', 'application/msword'],

            //doc
            ['res_docx.png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],

            //pdf
            ['res_pdf.png', 'application/pdf'],

            //zip
            ['res_zip.png', 'application/zip'],
            ['res_rar.png', 'application/x-rar-compressed'],

            //rar
            ['res_archive.png', 'application/x-gtar'],
            ['res_archive.png', 'application/x-7z-compressed'],

            //gz
            ['res_gz.png', 'application/x-compressed'],
            ['res_gz.png', 'application/x-gzip'],
            ['res_gz.png', 'multipart/x-gzip'],

            //tar
            ['res_tar.png', 'application/x-tar'],

            //array('res_dot.png') alias for msword

            //odp
            ['res_odp.png', 'application/vnd.oasis.opendocument.presentation'],

            //ods
            ['res_ods.png', 'application/vnd.oasis.opendocument.spreadsheet'],

            //array('res_pps.png') alias for powerpoint
            //array('res_psp.png') couldn't find mime type

            ['res_rtf.png', 'application/rtf'],
            ['res_rtf.png', 'application/x-rtf'],
            ['res_rtf.png', 'text/richtext'],
        ];
    }

    private function isDirectoryEmpty($dirName)
    {
        $files = [];
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

    private function createShortcutFromRelativeUrl($url, $workspace = null)
    {
        $ds = DIRECTORY_SEPARATOR;

        try {
            $originalIconLocation = "{$this->rootDir}{$ds}..{$ds}web{$ds}{$url}";
            $shortcutLocation = $this->creator->shortcutThumbnail($originalIconLocation, $workspace);
        } catch (\Exception $e) {
            $this->log("Couldn't create the shortcut icon: using the default one...", LogLevel::ERROR);
            $this->log(get_class($e).": {$e->getMessage()}", LogLevel::ERROR);
            $shortcutLocation = "{$this->rootDir}{$ds}..{$ds}web{$ds}{$url}";
        }

        $tmpRelativeUrl = (strstr($shortcutLocation, 'bundles')) ?
            strstr($shortcutLocation, 'bundles') :
            strstr($shortcutLocation, $this->basepath);

        return str_replace('\\', '/', $tmpRelativeUrl);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }
}
