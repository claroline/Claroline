<?php

namespace Claroline\ThemeBundle\Manager;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Manager\File\ArchiveManager;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ThemeBundle\Entity\Icon\IconItem;
use Claroline\ThemeBundle\Entity\Icon\IconSet;
use Symfony\Component\Filesystem\Filesystem;

class IconSetBuilderManager
{
    /**
     * Path for icons installed through core and plugins.
     * NB: the path is prefixed with the bundle path.
     *
     * @var string
     */
    const INSTALLED_ICON_PATH = 'Resources'.DIRECTORY_SEPARATOR.'icons';

    /** @var Filesystem */
    private $filesystem;
    /** @var string */
    private $iconSetsDir;
    /** @var string */
    private $iconSetsWebDir;
    /** @var string */
    private $webDir;

    /** @var ObjectManager */
    private $om;
    /** @var TempFileManager */
    private $tempManager;
    /** @var ArchiveManager */
    private $archiveManager;

    public function __construct(
        string $webDir,
        string $iconSetsWebDir,
        string $iconSetsDir,
        ObjectManager $om,
        TempFileManager $tempManager,
        ArchiveManager $archiveManager
    ) {
        $this->filesystem = new FileSystem();
        $this->webDir = $webDir;
        $this->iconSetsWebDir = $iconSetsWebDir;
        $this->iconSetsDir = $iconSetsDir;

        $this->om = $om;
        $this->tempManager = $tempManager;
        $this->archiveManager = $archiveManager;
    }

    public function zip(string $setName): string
    {
        $fileBag = new FileBag();
        $setPath = $this->getSetDir($setName);

        // put all icons in the archive
        if ($this->filesystem->exists($setPath)) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($setPath, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS));

            foreach ($files as $file) {
                // Skip directories (they would be added automatically)
                if (!$file->isDir()) {
                    // Get real and relative path for current file
                    $filePath = $file->getPathname();
                    $relativePath = substr($filePath, strlen($setPath) + 1);

                    $fileBag->add($relativePath, $filePath);
                }
            }
        }

        $archive = $this->archiveManager->create(null, $fileBag);
        $archivePath = $archive->filename; // we cannot read the filename once the archive is closed
        $archive->close();

        return $archivePath;
    }

    public function generateFromArchive(\SplFileInfo $archiveFile, string $setName): void
    {
        // create a temp dir to extract icon files
        $setPath = $this->tempManager->getDirectory().DIRECTORY_SEPARATOR.$setName;
        if (!$this->filesystem->exists($setPath)) {
            $this->filesystem->mkdir($setPath, 0775);
        }

        // extract icon files
        $archive = new \ZipArchive();
        $archive->open($archiveFile->getPathname());
        $this->archiveManager->extractFiles($archive, $setPath);

        $this->generateSet($setPath, $this->getAllMimeTypes());
        $this->om->flush();

        // remove temps icons files
        $this->filesystem->remove($setPath);
    }

    public function generateFromPlugin(string $pluginPath, ?array $customMimeTypes = []): void
    {
        $iconsPath = $pluginPath.DIRECTORY_SEPARATOR.static::INSTALLED_ICON_PATH;
        if (!$this->filesystem->exists($iconsPath)) {
            // no icon defined in the plugin, we can stop now
            return;
        }

        $setIterator = new \DirectoryIterator($iconsPath);
        foreach ($setIterator as $setDir) {
            // icons directory should contain a sub dir by declared set
            if (!$setDir->isDir() || $setDir->isDot()) {
                continue;
            }

            $this->generateSet($setDir->getPathname(), $customMimeTypes);
        }
    }

    public function removeSetDir(string $setName): void
    {
        $setPath = $this->getSetDir($setName);

        if ($this->filesystem->exists($setPath)) {
            $this->filesystem->remove($setPath);
        }
    }

    private function generateSet(string $setPath, ?array $customMimeTypes = []): void
    {
        $relativeSetsUrl = $this->filesystem->makePathRelative($this->iconSetsWebDir, $this->webDir);

        $setName = pathinfo($setPath, PATHINFO_FILENAME);

        $setTypeIterator = new \DirectoryIterator($setPath);
        foreach ($setTypeIterator as $setTypeDir) {
            if (!$setTypeDir->isDir() || $setTypeDir->isDot()) {
                continue;
            }

            $setTypeName = pathinfo($setTypeDir->getFilename(), PATHINFO_FILENAME);

            if (!in_array($setTypeName, [IconSet::RESOURCE_ICON_SET, IconSet::DATA_ICON_SET, IconSet::WIDGET_ICON_SET])) {
                // not a supported set type
                continue;
            }

            $iconSet = $this->generateSetType($setName, $setTypeName);

            $iconIterator = new \DirectoryIterator($setTypeDir->getPathname());
            foreach ($iconIterator as $iconFile) {
                if ($iconFile->isDir() || $iconFile->isDot()) {
                    continue;
                }

                $iconName = pathinfo($iconFile->getFilename(), PATHINFO_FILENAME);
                $iconExt = pathinfo($iconFile->getFilename(), PATHINFO_EXTENSION);
                $isSvg = 'image/svg+xml' === mime_content_type($iconFile->getPathname()) || 'svg' === $iconExt;

                // copy icon file in app public dir to make it accessible by ui
                $publicPath = $this->iconSetsWebDir.DIRECTORY_SEPARATOR.$setName.DIRECTORY_SEPARATOR.$setTypeName.DIRECTORY_SEPARATOR.$iconFile->getFilename();
                $this->filesystem->copy($iconFile->getPathname(), $publicPath);

                $mimeTypes = static::getIconMimeTypes($iconName, $customMimeTypes);
                foreach ($mimeTypes as $mimeType) {
                    $iconItem = $this->om->getRepository(IconItem::class)->findOneBy(['iconSet' => $iconSet, 'mimeType' => $mimeType]);
                    if (!$iconItem) {
                        $iconItem = new IconItem();
                        $iconItem->setIconSet($iconSet);
                        $iconItem->setMimeType($mimeType);
                    }

                    $iconItem->setName($iconName);
                    $iconItem->setRelativeUrl(
                        $relativeSetsUrl.DIRECTORY_SEPARATOR.$setName.DIRECTORY_SEPARATOR.$setTypeName.DIRECTORY_SEPARATOR.$iconFile->getFilename()
                    );
                    $iconItem->setSvg($isSvg);

                    $this->om->persist($iconItem);
                }
            }
        }
    }

    private function generateSetType(string $setName, string $setTypeName): IconSet
    {
        $iconSet = $this->om->getRepository(IconSet::class)->findOneBy(['name' => $setName, 'type' => $setTypeName]);
        if (empty($iconSet)) {
            $iconSet = new IconSet();
            $iconSet->setType($setTypeName);
            $iconSet->setName($setName);
            if ('claroline' === $setName) {
                $iconSet->setDefault(true);
            }

            $this->om->persist($iconSet);
            $this->om->flush();
        }

        $this->createSetTypeDir($setName, $setTypeName);

        return $iconSet;
    }

    private function createSetTypeDir(string $setName, string $setTypeName): void
    {
        $setPath = $this->getSetDir($setName).DIRECTORY_SEPARATOR.$setTypeName;

        if (!$this->filesystem->exists($setPath)) {
            $this->filesystem->mkdir($setPath, 0775);
        }
    }

    private function getSetDir(string $setName): string
    {
        return $this->iconSetsWebDir.DIRECTORY_SEPARATOR.$setName;
    }

    /**
     * Gets the list of mime types for which the icon is used.
     */
    private static function getIconMimeTypes(string $iconName, ?array $customMimeTypes = []): array
    {
        if (!empty($customMimeTypes) && !empty($customMimeTypes[$iconName])) {
            return $customMimeTypes[$iconName];
        }

        return ['custom/'.$iconName];
    }

    /**
     * We use IconItems from the default IconSet in order to rebuild the icon file <> mime type association.
     * This is partially declared in the config.yml of plugins (see `resource_icons` key).
     * We should only store one line by icon file in order to avoid it.
     */
    private function getAllMimeTypes(): array
    {
        $mimeTypes = [];

        /** @var IconSet[] $defaultSets */
        $defaultSets = $this->om->getRepository(IconSet::class)->findBy(['default' => true]);
        foreach ($defaultSets as $defaultSet) {
            foreach ($defaultSet->getIcons() as $icon) {
                if (empty($mimeTypes[$icon->getName()])) {
                    $mimeTypes[$icon->getName()] = [];
                }

                if (!in_array($icon->getMimeType(), $mimeTypes[$icon->getName()])) {
                    $mimeTypes[$icon->getName()][] = $icon->getMimeType();
                }
            }
        }

        return $mimeTypes;
    }
}
