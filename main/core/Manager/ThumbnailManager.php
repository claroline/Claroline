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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceThumbnail;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Library\Utilities\ThumbnailCreator;
use Claroline\CoreBundle\Repository\ResourceIconRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @DI\Service("claroline.manager.thumbnail_manager")
 */
class ThumbnailManager
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
     * Creates a custom ResourceThumbnail entity from a File (wich should contain an image).
     * (for instance if the thumbnail of a resource is changed).
     *
     * @param File      $file
     * @param Workspace $workspace (for the storage directory...)
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceThumbnail
     */
    public function createCustomThumbnail(File $file, Workspace $workspace = null)
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
            $thumbnail = new ResourceThumbnail();
            $thumbnail->setRelativeUrl($relativeUrl);
            $thumbnail->setMimeType('custom');
            $thumbnail->setShortcut(false);
            $thumbnail->setUuid(uniqid('', true));
            $this->om->persist($thumbnail);
            $this->om->endFlushSuite();

            return $thumbnail;
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
                $thumbnailPath = $this->creator->fromVideo($filePath, $newPath, 400, 250);
            } catch (\Exception $e) {
                //ffmpege extension might be missing
                $thumbnailPath = null;
            }
        }

        if ('image' === $baseMime) {
            try {
                $thumbnailPath = $this->creator->fromImage($filePath, $newPath, 400, 250);
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
     * Replace a node thumbnail.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode      $resource
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceThumbnail $thumbnail
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function replace(ResourceNode $resource, ResourceThumbnail $thumbnail)
    {
        $this->om->startFlushSuite();

        $resource->setThumbnail($thumbnail);
        $this->om->persist($resource);
        $this->om->endFlushSuite();

        return $resource;
    }
}
