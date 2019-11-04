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

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Icon\IconItem;
use Claroline\CoreBundle\Entity\Icon\IconSet;
use Claroline\CoreBundle\Entity\Icon\IconSetTypeEnum;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Icon\ResourceIconSetIconItemList;
use Claroline\CoreBundle\Library\Utilities\FileSystem;
use Claroline\CoreBundle\Repository\Icon\IconItemRepository;
use Claroline\CoreBundle\Repository\Icon\IconSetRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IconSetManager
{
    use LoggableTrait;

    /** @var ObjectManager */
    private $om;
    /** @var IconSetRepository */
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

    /**
     * @param $webDir
     * @param $iconSetsWebDir
     * @param $iconSetsDir
     * @param ObjectManager $om
     */
    public function __construct(
        $webDir,
        $iconSetsWebDir,
        $iconSetsDir,
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
     * @return array|\Claroline\CoreBundle\Entity\Icon\IconSet[]
     */
    public function listIconSetsByType($iconSetType)
    {
        return $this->iconSetRepo->findBy(['type' => $iconSetType]);
    }

    /**
     * @param IconSet|null $iconSet
     * @param bool|true    $includeDefault
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

    public function setActiveResourceIconSetByCname($cname, $force = false)
    {
        // Get active Icon Set
        $activeSet = $this->getActiveResourceIconSet();
        if (!$force && $activeSet->getCname() === $cname) {
            return true;
        }
        $newActiveSet = $this->iconSetRepo->findOneByCname($cname);
        if (empty($newActiveSet)) {
            return true;
        }
        $activeSet->setActive(false);
        $newActiveSet->setActive(true);
        $this->om->persist($activeSet);
        $this->om->persist($newActiveSet);
        $this->om->flush();

        return true;
    }

    /**
     * @param IconSet $iconSet
     */
    public function deleteIconSet(IconSet $iconSet)
    {
        if ($iconSet->isActive() || $iconSet->isDefault()) {
            throw new BadRequestHttpException('error_cannot_delete_active_default_icon_set');
        }
        $cname = $iconSet->getCname();
        $this->om->remove($iconSet);
        $this->om->flush();
        $this->deleteIconSetDirForCname($cname);
    }

    public function deleteAllResourceIconItemsForMimeType($mimeType)
    {
        $this->iconItemRepo->deleteAllByMimeType($mimeType);
    }

    /**
     * @param $cname
     */
    private function deleteIconSetDirForCname($cname)
    {
        $cnameDir = $this->iconSetsDir.DIRECTORY_SEPARATOR.$cname;
        if ($this->fs->exists($cnameDir)) {
            $this->fs->rmdir($cnameDir, true);
        }
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
                        $iconSet = $this->iconSetRepo->findOneBy(['name' => $name, 'type' => IconSetTypeEnum::RESOURCE_ICON_SET]);

                        if (!$iconSet) {
                            $iconSet = new IconSet();
                            $iconSet->setType(IconSetTypeEnum::RESOURCE_ICON_SET);
                            $iconSet->setName($name);

                            if ('claroline' === $name) {
                                $iconSet->setDefault(true);
                                $iconSet->setActive(true);
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

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
