<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 1/17/17
 */

namespace Claroline\ThemeBundle\Manager;

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\ThemeBundle\Entity\Icon\IconItem;
use Claroline\ThemeBundle\Entity\Icon\IconSet;
use Claroline\ThemeBundle\Library\Icon\ResourceIconSetIconItemList;
use Claroline\ThemeBundle\Repository\Icon\IconItemRepository;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class IconSetManager implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var ObjectManager */
    private $om;
    private $iconSetRepo;
    /** @var IconItemRepository */
    private $iconItemRepo;
    /** @var string */
    private $iconSetsDir;
    /** @var string */
    private $iconSetsWebDir;
    /** @var string */
    private $webDir;
    /** @var FileSystem */
    private $fs;

    public function __construct(
        string $webDir,
        string $iconSetsWebDir,
        string $iconSetsDir,
        ObjectManager $om,
        PlatformConfigurationHandler $ch
    ) {
        $this->fs = new FileSystem();
        $this->om = $om;
        $this->iconSetRepo = $om->getRepository(IconSet::class);
        $this->iconItemRepo = $om->getRepository(IconItem::class);
        $this->webDir = $webDir;
        $this->iconSetsWebDir = $iconSetsWebDir;
        $this->iconSetsDir = $iconSetsDir;
        $this->ch = $ch;
    }

    /**
     * @param $iconSetType
     *
     * @return array|IconSet[]
     */
    public function listIconSetsByType($iconSetType)
    {
        return $this->iconSetRepo->findBy(['type' => $iconSetType]);
    }

    /**
     * @param bool|true $includeDefault
     *
     * @return ResourceIconSetIconItemList
     */
    public function getIconSetIconsByType(IconSet $iconSet = null, $includeDefault = true)
    {
        $iconSetIconsList = new ResourceIconSetIconItemList();
        if (null !== $iconSet) {
            $iconSetIcons = $iconSet->getIcons()->toArray();
            $iconSetIconsList->addSetIcons($iconSetIcons);
        }
        if ($includeDefault) {
            $defaultSetIcons = $this->iconItemRepo->findIconsForResourceIconSetByMimeTypes(
                null,
                $iconSetIconsList->getSetIcons()->getMimeTypes()
            );
            $iconSetIconsList->addDefaultIcons($defaultSetIcons);
        }

        return $iconSetIconsList;
    }

    /**
     * @deprecated
     */
    public function getActiveResourceIconSet()
    {
        return $this->iconSetRepo->findOneByName($this->ch->getParameter('display.resource_icon_set'));
    }

    public function generateIconSets($iconsPath, array $mimeTypesList = [], $force = false)
    {
        $ds = DIRECTORY_SEPARATOR;
        $relativeSetsUrl = $this->fs->makePathRelative($this->iconSetsWebDir, $this->webDir);

        if ($iconsPath && $this->fs->exists($iconsPath)) {
            $this->log('Updating resource icons...');

            $setIterator = new \DirectoryIterator($iconsPath);

            foreach ($setIterator as $setDir) {
                if ($setDir->isDir()) {
                    $name = pathinfo($setDir->getFilename(), PATHINFO_FILENAME);

                    if (!in_array($name, ['.', ''])) {
                        $iconSet = $this->iconSetRepo->findOneBy(['name' => $name, 'type' => IconSet::RESOURCE_ICON_SET]);

                        if (!$iconSet) {
                            $iconSet = new IconSet();
                            $iconSet->setType(IconSet::RESOURCE_ICON_SET);
                            $iconSet->setName($name);

                            if ('claroline' === $name) {
                                $iconSet->setDefault(true);
                            }
                            $this->om->persist($iconSet);
                            $this->om->flush();
                        }
                        if (!$this->fs->exists($this->iconSetsWebDir.$ds.$name)) {
                            $this->fs->mkdir($this->iconSetsWebDir.$ds.$name, 0775);
                        }

                        $directory = opendir($iconsPath.$ds.$name);

                        while ($fileName = readdir($directory)) {
                            $filePath = $iconsPath.$ds.$name.$ds.$fileName;

                            if ($this->fs->exists($filePath) && is_file($filePath)) {
                                $relativeUrl = $relativeSetsUrl.$name.$ds.$fileName;
                                $this->fs->copy($filePath, $this->iconSetsWebDir.$ds.$name.$ds.$fileName);

                                $mimeTypes = $this->fetchResourcesMimeTypes($fileName, $mimeTypesList);

                                foreach ($mimeTypes as $mimeType) {
                                    $iconItem = $this->fetchIconItem($iconSet, $mimeType);

                                    if (!$iconItem) {
                                        $iconItem = new IconItem($iconSet, $relativeUrl, null, $mimeType);
                                    } elseif ($force) {
                                        $iconItem->setRelativeUrl($relativeUrl);
                                    }
                                    $this->om->persist($iconItem);
                                }
                            }
                        }
                        closedir($directory);
                    }
                }
            }
        }
    }

    public function fetchAllResourcesMimeTypes()
    {
        $mimeTypes = [];

        $defaultIconSet = $this->iconSetRepo->findOneBy(['default' => true]);

        if ($defaultIconSet) {
            foreach ($defaultIconSet->getIcons() as $iconItem) {
                $mimeType = $iconItem->getMimeType();

                if ($mimeType) {
                    $mimeTypes[$mimeType] = $mimeType;
                }
            }
        }
        ksort($mimeTypes);

        return array_values($mimeTypes);
    }

    public function uploadIcon(IconSet $iconSet, UploadedFile $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $iconSetName = $iconSet->getCname();

        if (!$this->fs->exists($this->iconSetsWebDir.$ds.$iconSetName)) {
            $this->fs->mkdir($this->iconSetsWebDir.$ds.$iconSetName, 0775);
        }
        $fileName = $file->getClientOriginalName();
        $file->move($this->iconSetsWebDir.$ds.$iconSetName, $fileName);

        return $this->fs->makePathRelative($this->iconSetsWebDir, $this->webDir).$iconSetName.$ds.$fileName;
    }

    public function updateIconItems(IconSet $iconSet, array $mimeTypes, $url)
    {
        $iconItems = [];

        $this->om->startFlushSuite();

        foreach ($mimeTypes as $mimeType) {
            $iconItem = $this->fetchIconItem($iconSet, $mimeType);

            if (!$iconItem) {
                $iconItem = new IconItem($iconSet, $url, null, $mimeType);
            }
            $iconItem->setRelativeUrl($url);
            $this->om->persist($iconItem);
            $iconItems[] = $iconItem;
        }

        $this->om->endFlushSuite();

        return $iconItems;
    }

    private function fetchResourcesMimeTypes($fileName, array $mimeTypesList)
    {
        $nameParts = explode('.', $fileName);

        if (1 < count($nameParts)) {
            unset($nameParts[count($nameParts) - 1]);
        }
        $name = implode('.', $nameParts);

        return isset($mimeTypesList[$name]) ? $mimeTypesList[$name] : ['custom/'.$name];
    }

    private function fetchIconItem(IconSet $iconSet, $mimeType)
    {
        $iconItems = $this->iconItemRepo->findBy(['iconSet' => $iconSet, 'mimeType' => $mimeType]);

        return 0 < count($iconItems) ? $iconItems[0] : null;
    }
}
