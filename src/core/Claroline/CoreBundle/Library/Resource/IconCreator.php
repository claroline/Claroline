<?php

namespace Claroline\CoreBundle\Library\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\File as ResourceFile;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\IconType;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.resource.icon_creator")
 */
class IconCreator
{
    private $container;
    /** @var EntityManager */
    private $em;
    private $ic;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->ic = $container->get('claroline.utilities.thumbnail_creator');
    }

    /**
     * Sets the correct ResourceIcon to the resource. Persist the resource is required
     * before firing this.
     *
     * @param AbstractResource $resource
     * @param boolean          $isFixture (for testing purpose)
     *
     * @return AbstractResource
     */
    public function setResourceIcon(AbstractResource $resource, $isFixture = false)
    {
        $type = $resource->getResourceType();

        if ($type->getName() !== 'file') {
            $icon = $this->getTypeIcon($type);
        } else {
            if ($resource->getMimeType() === null) {
                throw new \RuntimeException("The entity {$resource->getName()} as no mime type set");
            }
            $icon = $this->getFileIcon($resource, $isFixture);
        }

        $resource->setIcon($icon);

        return $resource;
    }

    /**
     * Create (if possible) and returns an icon for a file.
     *
     * @param File    $resource  the file
     * @param boolean $isFixture
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     *
     * @throws \InvalidArgumentException
     */
    public function getFileIcon(ResourceFile $resource, $isFixture)
    {
        $mimeElements = explode('/', $resource->getMimeType());

        // if video or img => generate the thumbnail, otherwise find an existing one.
        if (($mimeElements[0] === 'video' || $mimeElements[0] === 'image') && $isFixture == false) {
            $originalPath = $this->container->getParameter('claroline.param.files_directory')
                . DIRECTORY_SEPARATOR . $resource->getHashName();
            $newPath = $this->container->getParameter('claroline.param.thumbnails_directory')
                . DIRECTORY_SEPARATOR
                . $this->container->get('claroline.resource.utilities')->generateGuid() . ".png";

            $thumbnailPath = null;
            if ($mimeElements[0] === 'video') {
                try {
                    $thumbnailPath = $this->ic->fromVideo($originalPath, $newPath, 100, 100);
                } catch (\Exception $e) {
                    $thumbnailPath = null;
                    //error handling ? $thumbnailPath = null
                }
            }

            if ($mimeElements[0] === 'image') {
                try {
                    $thumbnailPath = $this->ic->fromImage($originalPath, $newPath, 100, 100);
                } catch (\Exception $e) {
                    $thumbnailPath = null;
                    //error handling ? $thumbnailPath = null
                }
            }

            if ($thumbnailPath !== null) {
                $thumbnailName = pathinfo($thumbnailPath, PATHINFO_BASENAME);
                $relativeUrl = "thumbnails/{$thumbnailName}";
                $icon = new ResourceIcon();
                $generatedIconType = $this->em
                    ->getRepository('ClarolineCoreBundle:Resource\IconType')
                    ->find(IconType::GENERATED);
                $icon->setIconType($generatedIconType);
                $icon->setIconLocation($newPath);
                $icon->setRelativeUrl($relativeUrl);
                $icon->setType('generated');
                $icon->setShortcut(false);
                $this->createShortcutIcon($icon);
                $this->em->persist($icon);

                return $icon;
            }
        }

        return $this->searchFileIcon($resource->getMimeType());
    }

    /**
     * Returns the icon for the specified ResourceType.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon the resource type
     */
    public function getTypeIcon(ResourceType $type)
    {
        $repo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon');
        $icon = $repo->findOneBy(array('type' => $type->getName(), 'iconType' => IconType::TYPE));

        if ($icon === null) {
            $icon = $repo->findOneBy(array('type' => 'default', 'iconType' => IconType::DEFAULT_ICON));
        }

        return $icon;
    }

    /**
     * Return the icon of a specified mimeType.
     * The most specific icon for the mime type will be returned.
     *
     * @param string $mimeType
     *
     * @return  \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     */
    public function searchFileIcon($mimeType)
    {
        $mimeElements = explode('/', $mimeType);
        $repo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon');

        $icon = $repo->findOneBy(array('type' => $mimeType, 'iconType' => IconType::COMPLETE_MIME_TYPE));

        if ($icon === null) {
            $icon = $repo->findOneBy(array('type' => $mimeElements[0], 'iconType' => IconType::BASIC_MIME_TYPE));

            if ($icon === null) {
                $icon = $repo->findOneBy(array('type' => 'file', 'iconType' => IconType::TYPE));
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
        $ds = DIRECTORY_SEPARATOR;
        try {
            $shortcutLocation = $this->ic->shortcutThumbnail($icon->getIconLocation());
        } catch (\Exception $e) {
            $shortcutLocation = "{$this->container->getParameter('kernel.root_dir')}{$ds}.."
            . "{$ds}web{$ds}bundles{$ds}clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}shortcut-default.png";
        }

        $shortcutIcon = new ResourceIcon();
        $shortcutIcon->setIconLocation($shortcutLocation);
        if (strstr($shortcutLocation, "bundles")) {
            $tmpRelativeUrl = strstr($shortcutLocation, "bundles");
        } else {
            $tmpRelativeUrl = strstr($shortcutLocation, "thumbnails");
        }
        $relativeUrl = str_replace('\\', '/', $tmpRelativeUrl);
        $shortcutIcon->setRelativeUrl($relativeUrl);
        $shortcutIcon->setIconType($icon->getIconType());
        $shortcutIcon->setType($icon->getType());
        $shortcutIcon->setShortcut(true);
        $icon->setShortcutIcon($shortcutIcon);
        $shortcutIcon->setShortcutIcon($shortcutIcon);
        $this->em->persist($icon);
        $this->em->persist($shortcutIcon);

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
        $ds = DIRECTORY_SEPARATOR;
        $iconName = $file->getClientOriginalName();
        $extension = pathinfo($iconName, PATHINFO_EXTENSION);
        $hashName = $this->container->get('claroline.resource.utilities')->generateGuid() . "." . $extension;
        $file->move($this->container->getParameter('claroline.param.thumbnails_directory'), $hashName);
        //entity creation
        $icon = new ResourceIcon();
        $icon->setIconLocation(
            "{$this->container->getParameter('claroline.param.thumbnails_directory')}{$ds}{$hashName}"
        );
        $icon->setRelativeUrl("thumbnails/{$hashName}");
        $customType = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\IconType')
            ->find(IconType::CUSTOM_ICON);
        $icon->setIconType($customType);
        $icon->setType('custom');
        $icon->setShortcut(false);
        $this->em->persist($icon);
        $this->createShortcutIcon($icon);

        return $icon;
    }
}