<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\IconType;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;

/**
 * Resource images data fixture.
 */
class LoadResourceImagesData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface $container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load (ObjectManager $manager)
    {
        $iconsType = array('type', 'generated', 'basic_mime_type', 'complete_mime_type', 'default', 'custom');
        $defaultIconType = null;
        $basicIconMimeType = null;
        $completeIconMimeType = null;

        foreach($iconsType as $type) {
            $iconType = new IconType();
            $iconType->setIconType($type);
            $manager->persist($iconType);
            switch($type) {
                case 'type':
                    $typeIconType = $iconType;
                    break;
                case 'basic_mime_type':
                    $basicIconMimeType = $iconType;
                    break;
                case 'complete_mime_type':
                    $completeIconMimeType = $iconType;
                    break;
                case 'default':
                    $defaultIconType = $iconType;
                    break;
                default:
                    break;
            }
        }

        $ds = DIRECTORY_SEPARATOR;
        $largeIconsWebFolder = "{$this->container->getParameter('kernel.root_dir')}{$ds}..{$ds}web{$ds}bundles{$ds}clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}";
        $relativeUrl = "bundles{$ds}clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}";

        /*
         * [1] thumbnail link
         * [2] icon link
         * [3] icontype
         * [4] type (either resource type or mime type-
         */

        //see www.webmaster-toolkit.com/mime-types.shtml for mime types
        $resourceImages = array(
            array($largeIconsWebFolder.'res_default.png', $relativeUrl.'res_default.png', $defaultIconType, 'default'),
            array($largeIconsWebFolder.'res_file.png', $relativeUrl.'res_file.png', $typeIconType, 'file'),
            array($largeIconsWebFolder.'res_folder.png', $relativeUrl.'res_folder.png', $typeIconType, 'directory'),
            array($largeIconsWebFolder.'res_text.png', $relativeUrl.'res_text.png', $completeIconMimeType, 'text/plain'),
            array($largeIconsWebFolder.'res_text.png', $relativeUrl.'res_text.png', $basicIconMimeType, 'text'),
            array($largeIconsWebFolder.'res_url.png', $relativeUrl.'res_url.png', $typeIconType, 'url'),
            array($largeIconsWebFolder.'res_exercice.png', $relativeUrl.'res_exercice.png', $typeIconType, 'exercice'),
            array($largeIconsWebFolder.'res_video.png', $relativeUrl.'res_video.png', $basicIconMimeType, 'video'),
            array($largeIconsWebFolder.'res_msexcel.png', $relativeUrl.'res_msexcel.png', $completeIconMimeType, 'application/excel'),
            array($largeIconsWebFolder.'res_mspowerpoint.png', $relativeUrl.'res_mspowerpoint.png', $completeIconMimeType, 'application/powerpoint'),
            array($largeIconsWebFolder.'res_msword.png', $relativeUrl.'res_msword.png', $completeIconMimeType, 'application/msword'),
            array($largeIconsWebFolder.'res_msword.png', $relativeUrl.'res_msword.png', $completeIconMimeType, 'application/vnd.oasis.opendocument.text'),
            array($largeIconsWebFolder.'res_pdf.png', $relativeUrl.'res_pdf.png', $completeIconMimeType, 'application/pdf'),
            array($largeIconsWebFolder.'res_image.png', $relativeUrl.'res_image.png', $basicIconMimeType, 'image'),
        );

        foreach ($resourceImages as $resourceImage) {
            $rimg = new ResourceIcon();
            $rimg->setIconLocation($resourceImage[0]);
            $rimg->setRelativeUrl($resourceImage[1]);
            $rimg->setIconType($resourceImage[2]);
            $rimg->setType($resourceImage[3]);
            $rimg->setShortcut(false);
            $manager->persist($rimg);

            $this->container->get('claroline.resource.icon_creator')->createShortcutIcon($rimg);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4;
    }
}