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

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Icon\IconItem;
use Claroline\CoreBundle\Entity\Icon\IconSet;
use Claroline\CoreBundle\Entity\Icon\IconSetTypeEnum;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Library\Icon\ResourceIconItemFilenameList;
use Claroline\CoreBundle\Library\Icon\ResourceIconSetIconItemList;
use Claroline\CoreBundle\Library\Utilities\ExtensionNotSupportedException;
use Claroline\CoreBundle\Library\Utilities\FileSystem;
use Claroline\CoreBundle\Library\Utilities\ThumbnailCreator;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\Icon\IconItemRepository;
use Claroline\CoreBundle\Repository\Icon\IconSetRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @DI\Service("claroline.manager.icon_set_manager")
 */
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
    /** @var ThumbnailCreator */
    private $thumbnailCreator;

    /**
     * @DI\InjectParams({
     *     "webDir"             = @DI\Inject("%claroline.param.web_dir%"),
     *     "iconSetsWebDir"     = @DI\Inject("%claroline.param.icon_sets_web_dir%"),
     *     "iconSetsDir"        = @DI\Inject("%claroline.param.icon_sets_directory%"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "thumbnailCreator"   = @DI\Inject("claroline.utilities.thumbnail_creator")
     * })
     *
     * @param $webDir
     * @param $iconSetsWebDir
     * @param $iconSetsDir
     * @param ObjectManager    $om
     * @param ThumbnailCreator $thumbnailCreator
     */
    public function __construct(
        $webDir,
        $iconSetsWebDir,
        $iconSetsDir,
        ObjectManager $om,
        ThumbnailCreator $thumbnailCreator
    ) {
        $this->fs = new FileSystem();
        $this->thumbnailCreator = $thumbnailCreator;
        $this->om = $om;
        $this->iconSetRepo = $om->getRepository('ClarolineCoreBundle:Icon\IconSet');
        $this->iconItemRepo = $om->getRepository('ClarolineCoreBundle:Icon\IconItem');
        $this->webDir = $webDir;
        $this->iconSetsWebDir = $iconSetsWebDir;
        $this->iconSetsDir = $iconSetsDir;
    }

    /**
     * @param $iconSetType
     *
     * @return array
     */
    public function listIconSetNamesByType($iconSetType)
    {
        $iconSets = $this->listIconSetsByType($iconSetType);
        $iconSetNames = [];
        foreach ($iconSets as $iconSet) {
            $iconSetNames[$iconSet->getCname()] = $iconSet->getName();
        }

        return $iconSetNames;
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
     * @return bool
     */
    public function isIconSetsDirWritable()
    {
        return $this->fs->isWritable($this->iconSetsDir);
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
        if ($iconSet !== null) {
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
     * @param $id
     *
     * @return IconSet
     */
    public function getIconSetById($id)
    {
        if ($id === null) {
            return null;
        }

        return $this->iconSetRepo->findOneById($id);
    }

    /**
     * @param $cname
     *
     * @return IconSet | null
     */
    public function getIconSetByCName($cname)
    {
        return $this->iconSetRepo->findOneByCname($cname);
    }

    /**
     * @return IconSet
     */
    public function getDefaultResourceIconSet()
    {
        return $this->iconSetRepo->findOneBy(['cname' => 'claroline', 'default' => true]);
    }

    /**
     * @param null $iconSetId
     *
     * @return ResourceIconItemFilenameList
     */
    public function getResourceIconSetIconNamesForMimeTypes($iconSetId = null)
    {
        if ($iconSetId !== null) {
            $icons = $this->iconItemRepo->findByIconSet($iconSetId);
        } else {
            $icons = $this->iconItemRepo->findIconsForResourceIconSetByMimeTypes();
        }

        return new ResourceIconItemFilenameList($icons);
    }

    /**
     * Many mimeTypes have the same icon. Group these mime types together under the same filename.
     *
     * @param IconSet $iconSet
     * @param $mimeType
     *
     * @return array|\Claroline\CoreBundle\Entity\Icon\IconItem[]
     */
    public function getIconItemsByIconSetAndMimeType(IconSet $iconSet, $mimeType)
    {
        return $this->iconItemRepo->findBy(['iconSet' => $iconSet, 'mimeType' => $mimeType]);
    }

    /**
     * @param IconSet $iconSet
     * @param $iconNamesForType
     *
     * @return IconSet
     */
    public function createNewResourceIconSet(IconSet $iconSet, $iconNamesForType)
    {
        // Persist new Set
        $this->om->persist($iconSet);
        $this->om->flush();
        // Create icon set's folder
        $this->createIconSetDirForCname($iconSet->getCname());
        $this->extractResourceIconSetZipAndReturnNewIconItems($iconSet, $iconNamesForType);
        $this->om->flush();

        return $iconSet;
    }

    /**
     * @param IconSet $iconSet
     * @param $iconNamesForType
     *
     * @return IconSet
     */
    public function updateResourceIconSet(IconSet $iconSet, $iconNamesForType)
    {
        $this->extractResourceIconSetZipAndReturnNewIconItems($iconSet, $iconNamesForType);
        $this->om->persist($iconSet);
        $this->om->flush();

        return $iconSet;
    }

    /**
     * @param ResourceIcon $icon
     * @param null         $newRelativeUrl
     */
    public function addOrUpdateIconItemToDefaultResourceIconSet(ResourceIcon $icon, $newRelativeUrl = null)
    {
        $resourceIconSet = $this->getDefaultResourceIconSet();
        $existingIcons = $this->getIconItemsByIconSetAndMimeType($resourceIconSet, $icon->getMimeType());
        $newRelativeUrl = empty($newRelativeUrl) ? $icon->getRelativeUrl() : $newRelativeUrl;
        if (empty($existingIcons) || count($existingIcons) < 1) {
            $this->createIconItemForResourceIconSet(
                $resourceIconSet,
                $newRelativeUrl,
                $icon
            );
        } else {
            foreach ($existingIcons as $existingIcon) {
                $existingIcon->setResourceIcon($icon);
                $this->updateIconItemForResourceIconSet($resourceIconSet, $newRelativeUrl, $existingIcon);
            }
            // Update resource icons for all other existing icon sets
            $this->iconItemRepo->updateResourceIconForAllSets($icon);
        }
    }

    public function getActiveResourceIconSet()
    {
        return $this->iconSetRepo->findOneBy(['active' => true, 'type' => IconSetTypeEnum::RESOURCE_ICON_SET]);
    }

    public function getResourceIconSetStampIcon(IconSet $iconSet = null)
    {
        if ($iconSet === null) {
            $iconSet = new IconSet();
        }
        if (!empty($iconSet->getResourceStampIcon())) {
            $iconRelativeUrl = $iconSet->getResourceStampIcon();
        } else {
            $iconRelativeUrl = $this->thumbnailCreator->getDefaultStampRelativeUrl();
        }

        return new IconItem($iconSet, $iconRelativeUrl, 'shortcut', 'shortcut');
    }

    public function setActiveResourceIconSetByCname($cname)
    {
        // Get active Icon Set
        $activeSet = $this->getActiveResourceIconSet();
        if ($activeSet->getCname() === $cname) {
            return true;
        }
        $newActiveSet = $this->getIconSetByCName($cname);
        if (empty($newActiveSet)) {
            return true;
        }

        // Set all ResourceIcons to default set's icons (this way we make sure that even if some icons
        // don't exist in this set they will be replaced by default icons and not by last active theme's icons)
        if (!$newActiveSet->isDefault() || !$newActiveSet->getCname() === 'claroline') {
            $this->iconItemRepo->updateResourceIconsByIconSetIcons($this->getDefaultResourceIconSet());
        }
        // Then update with new set icons
        $this->iconItemRepo->updateResourceIconsByIconSetIcons($newActiveSet);
        // Regenerate shortcut icon for all resource icons in database
        $this->regenerateShortcutForAllResourceIcons($newActiveSet->getResourceStampIcon());
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

    public function deleteResourceIconSetIconByFilename(IconSet $iconSet, $filename)
    {
        if ($iconSet->isDefault()) {
            throw new BadRequestHttpException('error_cannot_delete_default_icon_set_icon');
        }
        // On shortcut stamp remove, then delete it from icon set and regenerate shortcut thumbnails for all the
        // icons on the set using default icon
        if ($filename === 'shortcut') {
            $this->fs->remove($this->getAbsolutePathForResourceIcon($iconSet->getResourceStampIcon()));
            $iconSet->setResourceStampIcon(null);
            // If icon set is active, regenerate shortcut for all resource icons
            if ($iconSet->isActive()) {
                $this->regenerateShortcutForAllResourceIcons(null);
            }
            $this->om->persist($iconSet);
            $this->om->flush();

            return $this->thumbnailCreator->getDefaultStampRelativeUrl();
        }
        $iconNamesForTypes = $this->getResourceIconSetIconNamesForMimeTypes($iconSet->getId());
        // For all the rest icons, remove them from set and restore defaults if iconset is active
        $newIconRelativeUrl = null;
        $iconItemFilename = $iconNamesForTypes->getItemByKey($filename);
        if (empty($iconItemFilename)) {
            return $newIconRelativeUrl;
        }

        $mimeTypes = $iconItemFilename->getMimeTypes();
        if (empty($mimeTypes)) {
            return $newIconRelativeUrl;
        }
        //Delete icons from icon set in database
        foreach ($mimeTypes as $mimeType) {
            $icon = $iconNamesForTypes->getIconByMimeType($mimeType);
            $this->om->remove($icon);
        }
        //If iconset is active icon set, restore resource icons to default values
        if ($iconSet->isActive()) {
            // Restore default icons for these mimetypes
            $this->iconItemRepo->updateResourceIconsByIconSetIcons(null, $mimeTypes);
            $this->regenerateShortcutForResourceIconsByMimeTypes($mimeTypes, $iconSet->getResourceStampIcon());
        }
        $this->om->flush();
        // Remove both icon and shortcut icon from icon set folder
        $this->fs->remove($this->getAbsolutePathForResourceIcon($iconItemFilename->getRelativeUrl()));
        // Default icon relative path
        $defaultIcons = $this->iconItemRepo
            ->findIconsForResourceIconSetByMimeTypes(null, null, [$mimeTypes[0]], false);

        if (!empty($defaultIcons)) {
            $newIconRelativeUrl = $defaultIcons[0]->getRelativeUrl();
        }

        return $newIconRelativeUrl;
    }

    public function uploadNewResourceIconSetIconByFilename(IconSet $iconSet, UploadedFile $newFile, $filename)
    {
        if ($iconSet->isDefault()) {
            throw new BadRequestHttpException('error_cannot_update_default_icon_set_icon');
        }
        $iconSetDir = $this->iconSetsWebDir.DIRECTORY_SEPARATOR.$iconSet->getCname();
        // Upload file and create shortcut
        $newIconFilename = $filename.'.'.$newFile->getClientOriginalExtension();
        $newFile->move(
            $iconSetDir,
            $newIconFilename
        );
        $newIconPath = $iconSetDir.DIRECTORY_SEPARATOR.$newIconFilename;
        $iconItemFilenameList = $this->getResourceIconSetIconNamesForMimeTypes($iconSet->getId());
        // If submitted icon is stamp icon, then set new stamp icon to icon set and regenerate all thumbnails
        if ($filename === 'shortcut') {
            $relativeStampIcon = $this->getRelativePathForResourceIcon($newIconPath);
            $iconSet->setResourceStampIcon($relativeStampIcon);
            $this->om->persist($iconSet);
            // If icon set is active, regenerate shortcut for all resource icons
            if ($iconSet->isActive()) {
                $this->regenerateShortcutForAllResourceIcons($relativeStampIcon);
            }
            $this->om->flush();

            return $relativeStampIcon;
        }
        // Test if icon already exists in set
        $iconItemFilename = $iconItemFilenameList->getItemByKey($filename);
        $alreadyInSet = true;
        if (empty($iconItemFilename)) {
            // If icon doesn't exist in set, get it by default set
            $iconItemFilenameList = $this->getResourceIconSetIconNamesForMimeTypes();
            $iconItemFilename = $iconItemFilenameList->getItemByKey($filename);
            $alreadyInSet = false;
            if (empty($iconItemFilename)) {
                return null;
            }
        }
        foreach ($iconItemFilename->getMimeTypes() as $type) {
            // If icon don't exist, create it, otherwise update it's url in case of extension change
            $icon = $iconItemFilenameList->getIconByMimeType($type);
            if (!$alreadyInSet) {
                $resourceIcon = $icon->getResourceIcon();
                $this->createIconItemForResourceIconSet(
                    $iconSet,
                    $this->getRelativePathForResourceIcon($newIconPath),
                    $resourceIcon
                );
            } else {
                $this->updateIconItemForResourceIconSet(
                    $iconSet,
                    $this->getRelativePathForResourceIcon($newIconPath),
                    $icon
                );
            }
        }
        $this->om->flush();

        return $this->getRelativePathForResourceIcon($newIconPath);
    }

    public function deleteAllResourceIconItemsForMimeType($mimeType)
    {
        $this->iconItemRepo->deleteAllByMimeType($mimeType);
    }

    /**
     * @param $cname
     */
    private function createIconSetDirForCname($cname)
    {
        $cnameDir = $this->iconSetsDir.DIRECTORY_SEPARATOR.$cname;
        if ($this->fs->exists($cnameDir)) {
            $this->fs->rmdir($cnameDir, true);
        }
        $this->fs->mkdir($cnameDir, 0775);
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

    /**
     * Extracts icons from provided zipfile into iconSet directory.
     *
     * @param IconSet $iconSet
     * @param $iconSetIconItemList
     *
     * @return array
     */
    private function extractResourceIconSetZipAndReturnNewIconItems(
        IconSet $iconSet,
        ResourceIconSetIconItemList $iconSetIconItemList
    ) {
        $ds = DIRECTORY_SEPARATOR;
        $zipFile = $iconSet->getIconsZipfile();
        $cname = $iconSet->getCname();
        $iconSetDir = $this->iconSetsWebDir.$ds.$cname;
        if (!empty($zipFile)) {
            $zipArchive = new \ZipArchive();
            if ($zipArchive->open($zipFile) === true) {
                //Test to see if a resource stamp icon is present in zip file
                $resourceStamp = $this->extractResourceStampIconFromZip($zipArchive, $iconSetDir);
                if (!empty($resourceStamp)) {
                    $iconSet->setResourceStampIcon($resourceStamp);
                    $this->om->persist($iconSet);
                    if ($iconSet->isActive()) {
                        $this->regenerateShortcutForAllResourceIcons($resourceStamp);
                    }
                }
                //List filenames and extract all files without subfolders
                for ($i = 0; $i < $zipArchive->numFiles; ++$i) {
                    $file = $zipArchive->getNameIndex($i);
                    $fileinfo = pathinfo($file);
                    $filename = $fileinfo['filename'];
                    //If file associated with one of mimeTypes then extract it. Otherwise don't
                    $alreadyInSet = $iconSetIconItemList->isInSetIcons($filename);
                    $iconItemFilenameList = $alreadyInSet ?
                        $iconSetIconItemList->getSetIcons() :
                        $iconSetIconItemList->getDefaultIcons();
                    $iconNameTypes = $iconItemFilenameList->getItemByKey($filename);
                    if (!empty($iconNameTypes)) {
                        $iconPath = $iconSetDir.$ds.$fileinfo['basename'];
                        $this->fs->remove($iconSetDir.DIRECTORY_SEPARATOR.$fileinfo['basename']);
                        $zipArchive->extractTo($iconSetDir, [$file]);

                        foreach ($iconNameTypes->getMimeTypes() as $type) {
                            // If icon don't exist, create it, otherwise update it's url in case of extension change
                            $icon = $iconItemFilenameList->getIconByMimeType($type);
                            if (!$alreadyInSet) {
                                $resourceIcon = $icon->getResourceIcon();
                                $this->createIconItemForResourceIconSet(
                                    $iconSet,
                                    $this->getRelativePathForResourceIcon($iconPath),
                                    $resourceIcon
                                );
                            } else {
                                $this->updateIconItemForResourceIconSet(
                                    $iconSet,
                                    $this->getRelativePathForResourceIcon($iconPath),
                                    $icon
                                );
                            }
                        }
                    }
                }
                $zipArchive->close();
            }
        }
    }

    /**
     * @param $absolutePath
     *
     * @return mixed
     */
    private function getRelativePathForResourceIcon($absolutePath)
    {
        if (empty($absolutePath)) {
            return null;
        }
        $pathInfo = pathinfo($absolutePath);

        return $this->fs->makePathRelative($pathInfo['dirname'], $this->webDir).$pathInfo['basename'];
    }

    private function getAbsolutePathForResourceIcon($relativePath)
    {
        if (empty($relativePath)) {
            return null;
        }

        return $this->webDir.DIRECTORY_SEPARATOR.$relativePath;
    }

    /**
     * @param IconSet $iconSet
     * @param $iconPath
     * @param ResourceIcon $icon
     *
     * @return array
     */
    private function createIconItemForResourceIconSet(
        IconSet $iconSet,
        $iconPath,
        ResourceIcon $icon
    ) {
        $iconItem = new IconItem(
            $iconSet,
            $iconPath,
            null,
            $icon->getMimeType(),
            null,
            false,
            $icon
        );

        $this->om->persist($iconItem);
        if ($iconSet->isActive() && !$iconSet->isDefault()) {
            $icon->setRelativeUrl($iconPath);
            $this->om->persist($icon);
            $this->regenerateShortcutForResourceIcon($icon, $iconSet->getResourceStampIcon());
        }
    }

    /**
     * @param IconSet $iconSet
     * @param $iconPath
     * @param IconItem $icon
     */
    private function updateIconItemForResourceIconSet(
        IconSet $iconSet,
        $iconPath,
        IconItem $icon
    ) {
        $icon->setRelativeUrl($iconPath);
        $this->om->persist($icon);
        if ($iconSet->isActive()) {
            $resourceIcon = $icon->getResourceIcon();
            $resourceIcon->setRelativeUrl($iconPath);
            $this->om->persist($resourceIcon);
            $this->regenerateShortcutForResourceIcon($resourceIcon, $iconSet->getResourceStampIcon());
        }
    }

    private function extractResourceStampIconFromZip(\ZipArchive $zip, $iconSetDir)
    {
        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $file = $zip->getNameIndex($i);
            $fileinfo = pathinfo($file);
            $filename = $fileinfo['filename'];
            if ($filename === 'shortcut') {
                $zip->extractTo($iconSetDir, [$file]);

                return $this->getRelativePathForResourceIcon($iconSetDir.DIRECTORY_SEPARATOR.$fileinfo['basename']);
            }
        }

        return null;
    }

    /**
     * @param $stampRelativePath
     */
    private function regenerateShortcutForAllResourceIcons($stampRelativePath)
    {
        $resourceIconRepo = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon');
        $icons = $resourceIconRepo->findBy(['isShortcut' => false]);
        $this->regenerateShortcutForResourceIcons($icons, $stampRelativePath);
    }

    /**
     * @param $mimeTypes
     * @param $stampRelativePath
     */
    private function regenerateShortcutForResourceIconsByMimeTypes($mimeTypes, $stampRelativePath)
    {
        $resourceIconRepo = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon');
        $icons = $resourceIconRepo->findByMimeTypes($mimeTypes, false);
        $this->regenerateShortcutForResourceIcons($icons, $stampRelativePath);
    }

    /**
     * @param $icons
     * @param $stampRelativePath
     */
    private function regenerateShortcutForResourceIcons($icons, $stampRelativePath)
    {
        foreach ($icons as $icon) {
            try {
                $this->regenerateShortcutForResourceIcon($icon, $stampRelativePath);
            } catch (ExtensionNotSupportedException $ense) {
                $this->log(
                    "Error: Extension '".$ense->getExtension().
                    "' not found or not supported for file '".$icon->getRelativeUrl()."'"
                );
            } catch (FileNotFoundException $fnfe) {
                $this->log("Error: File '".$icon->getRelativeUrl()."' not found!");
            }
        }
    }

    /**
     * @param ResourceIcon $icon
     * @param $stampRelativePath
     *
     * @throws \Claroline\CoreBundle\Library\Utilities\ExtensionNotSupportedException
     * @throws \Claroline\CoreBundle\Library\Utilities\UnloadedExtensionException
     */
    private function regenerateShortcutForResourceIcon(ResourceIcon $icon, $stampRelativePath)
    {
        $shortcutIcon = $icon->getShortcutIcon();
        $iconAbsoluteUrl = $this->getAbsolutePathForResourceIcon($icon->getRelativeUrl());
        if (!empty($shortcutIcon) && $this->fs->exists($iconAbsoluteUrl)) {
            $shortcutFile = $this->thumbnailCreator->shortcutThumbnail(
                $iconAbsoluteUrl,
                $this->getAbsolutePathForResourceIcon($stampRelativePath)
            );
            $shortcutIconRelativeUrl = trim($shortcutIcon->getRelativeUrl());
            if (!empty($shortcutIconRelativeUrl) && !is_dir($this->webDir.$shortcutIconRelativeUrl)) {
                $this->fs->remove($this->webDir.$shortcutIconRelativeUrl);
            }
            $shortcutIcon->setRelativeUrl($this->getRelativePathForResourceIcon($shortcutFile));
            $this->om->persist($shortcutIcon);
        }
    }

    public function addDefaultIconSets()
    {
        $defaultDir = __DIR__.'/../Resources/public/images/resources/defaults';
        $iterator = new \DirectoryIterator($defaultDir);

        foreach ($iterator as $archive) {
            if ($archive->isFile()) {
                $name = pathinfo($archive->getFilename(), PATHINFO_FILENAME);

                //_claroline always first item because they are the default icon set
                if ($name === '_claroline') {
                    $name = 'claroline';
                }

                if ($this->iconSetRepo->findOneByName($name)) {
                    $iconSet = $this->iconSetRepo->findOneByName($name);
                    $new = false;
                } else {
                    $iconSet = new IconSet();
                    $iconSet->setType(IconSetTypeEnum::RESOURCE_ICON_SET);
                    $iconSet->setName($name);
                    $new = true;
                }

                if ($name === 'claroline') {
                    $iconSet->setDefault(true);
                }

                $iconSet->setIconsZipfile($archive->getPathname());
                $this->om->persist($iconSet);
                $this->om->flush();
                $iconNamesForTypes = $this->getIconSetIconsByType($iconSet);
                if ($new) {
                    $this->log('Adding new icon set: '.$name);
                    $this->createNewResourceIconSet($iconSet, $iconNamesForTypes);
                } else {
                    $this->log('Updating icon set: '.$name);
                    $this->updateResourceIconSet($iconSet, $iconNamesForTypes);
                }
            }
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
