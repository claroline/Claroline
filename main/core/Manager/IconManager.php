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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Library\Utilities\ThumbnailCreator;
use Claroline\CoreBundle\Repository\ResourceIconRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\File\File;

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
    /** @var string */
    private $iconSetRepo;
    /** @var FileUtilities */
    private $fu;

    /**
     * @DI\InjectParams({
     *     "creator"  = @DI\Inject("claroline.utilities.thumbnail_creator"),
     *     "fileDir"  = @DI\Inject("%claroline.param.files_directory%"),
     *     "thumbDir" = @DI\Inject("%claroline.param.thumbnails_directory%"),
     *     "rootDir"  = @DI\Inject("%kernel.root_dir%"),
     *     "ut"       = @DI\Inject("claroline.utilities.misc"),
     *     "om"       = @DI\Inject("claroline.persistence.object_manager"),
     *     "basepath" = @DI\Inject("%claroline.param.relative_thumbnail_base_path%"),
     *     "fu"       = @DI\Inject("claroline.utilities.file"),
     *     "pdir"     = @DI\Inject("%claroline.param.public_files_directory%"),
     *     "webdir"   = @DI\Inject("%claroline.param.web_directory%"),
     * })
     */
    public function __construct(
        ThumbnailCreator $creator,
        $fileDir,
        $thumbDir,
        $rootDir,
        ClaroUtilities $ut,
        ObjectManager $om,
        $basepath,
        FileUtilities $fu,
        $pdir,
        $webdir
    ) {
        $this->creator = $creator;
        $this->repo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon');
        $this->iconSetRepo = $om->getRepository('ClarolineCoreBundle:Icon\IconSet');
        $this->fileDir = $fileDir;
        $this->thumbDir = $thumbDir;
        $this->rootDir = $rootDir;
        $this->ut = $ut;
        $this->om = $om;
        $this->basepath = $basepath;
        $this->fu = $fu;
        $this->pdir = $pdir;
        $this->webdir = $webdir;
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
        if (('video' === $mimeElements[0] || 'image' === $mimeElements[0])) {
            $this->om->startFlushSuite();
            $publicFile = $this->createFromFile(
                $this->fileDir.$ds.$resource->getHashName(),
                $mimeElements[0],
                $workspace
            );

            if ($publicFile) {
                $thumbnailPath = $this->webdir.$ds.$publicFile->getUrl();
                $relativeUrl = ltrim(str_replace($this->webdir, '', $thumbnailPath), "{$ds}");
                $icon = new ResourceIcon();
                $icon->setMimeType('custom');
                $icon->setRelativeUrl($relativeUrl);
                $icon->setShortcut(false);
                $icon->setUuid(uniqid('', true));
                $this->om->persist($icon);
                $this->createShortcutIcon($icon, $workspace);
                $this->om->endFlushSuite();

                $this->fu->createFileUse(
                  $publicFile,
                  get_class($icon),
                  $icon->getUuid(),
                  'resource-thumbnail'
                );

                return $icon;
            }
            $this->om->endFlushSuite();
        }

        //default & fallback
        return $this->searchIcon($node->getMimeType());
    }

    public function listResourceIcons()
    {
        return $this->repo->findBaseIcons();
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

        if (null === $icon) {
            $icon = $this->repo->findOneByMimeType($mimeElements[0]);

            if (null === $icon) {
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

        $relativeUrl = $this->createShortcutFromRelativeUrl($icon->getRelativeUrl());
        $shortcutIcon = new ResourceIcon();
        $shortcutIcon->setRelativeUrl($relativeUrl);
        $shortcutIcon->setMimeType($icon->getMimeType());
        $shortcutIcon->setShortcut(true);
        $shortcutIcon->setUuid(uniqid('', true));
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
        $this->om->startFlushSuite();
        $mimeElements = explode('/', $file->getMimeType());
        $ds = DIRECTORY_SEPARATOR;

        $publicFile = $this->createFromFile(
            $file->getPathname(),
            $mimeElements[0],
            $workspace
        );
        if ($publicFile) {
            $thumbnailPath = $this->webdir.$ds.$publicFile->getUrl();
            $relativeUrl = ltrim(str_replace($this->webdir, '', $thumbnailPath), "{$ds}");
            //entity creation
            $icon = new ResourceIcon();
            $icon->setRelativeUrl($relativeUrl);
            $icon->setMimeType('custom');
            $icon->setShortcut(false);
            $icon->setUuid(uniqid('', true));
            $this->om->persist($icon);
            $this->createShortcutIcon($icon);
            $this->om->endFlushSuite();

            return $icon;
        }
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

        $newPath = $this->pdir.$ds.$this->fu->getActiveDirectoryName().$ds.$this->ut->generateGuid().'.png';

        $thumbnailPath = null;

        if ('video' === $baseMime) {
            try {
                $thumbnailPath = $this->creator->fromVideo($filePath, $newPath, 100, 100);
            } catch (\Exception $e) {
                //ffmpege extension might be missing
                $thumbnailPath = null;
            }
        }

        if ('image' === $baseMime) {
            try {
                $thumbnailPath = $this->creator->fromImage($filePath, $newPath, 100, 100);
            } catch (\Exception $e) {
                $thumbnailPath = null;
                //error handling ? $thumbnailPath = null
            }
        }

        if ($thumbnailPath) {
            return $this->fu->createFile(new File($thumbnailPath));
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceIcon $icon
     */
    public function delete(ResourceIcon $icon, Workspace $workspace = null)
    {
        if ('custom' === $icon->getMimeType()) {
            //search if this icon is used elsewhere (ie copy)
            $res = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findBy(['icon' => $icon]);

            if (count($res) <= 1 && false === $icon->isShortcut()) {
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
    public function removeImageFromThumbDir(ResourceIcon $icon)
    {
        $pathName = $this->rootDir.'/../web/'.$icon->getRelativeUrl();

        if (is_file($pathName) && file_exists($pathName)) {
            unlink($pathName);
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

    private function createShortcutFromRelativeUrl($url)
    {
        $ds = DIRECTORY_SEPARATOR;

        try {
            // Get active icon set stamp image to create thumbnail
            $stampImg = $this->iconSetRepo->findActiveRepositoryResourceStampIcon();
            $stampImg = (empty($stampImg)) ? null : "{$this->rootDir}{$ds}..{$ds}web{$ds}{$stampImg}";
            $originalIconLocation = "{$this->rootDir}{$ds}..{$ds}web{$ds}{$url}";
            $shortcutLocation = $this->creator->shortcutThumbnail($originalIconLocation, $stampImg);
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
