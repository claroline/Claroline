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

        $largeIconsWebFolder = 'bundles/clarolinecore/images/resources/icons/';
        $defaultIcon = null;

        /*
         * [1] thumbnail link
         * [2] icon link
         * [3] icontype
         * [4] type (either resource type or mime type-
         */

        //see www.webmaster-toolkit.com/mime-types.shtml for mime types
        $resourceImages = array(
            array($largeIconsWebFolder.'res_default.png', $defaultIcon, $defaultIconType, 'default'),
            array($largeIconsWebFolder.'res_file.png', $defaultIcon, $typeIconType, 'file'),
            array($largeIconsWebFolder.'res_folder.png', $defaultIcon, $typeIconType, 'directory'),
            array($largeIconsWebFolder.'res_text.png', $defaultIcon, $completeIconMimeType, 'text/plain'),
            array($largeIconsWebFolder.'res_text.png', $defaultIcon, $basicIconMimeType, 'text'),
            array($largeIconsWebFolder.'res_url.png', $defaultIcon, $typeIconType, 'url'),
            array($largeIconsWebFolder.'res_exercice.png', $defaultIcon, $typeIconType, 'exercice'),
            array($largeIconsWebFolder.'res_video.png', $defaultIcon, $basicIconMimeType, 'video'),
            array($largeIconsWebFolder.'res_msexcel.png', $defaultIcon, $completeIconMimeType, 'application/excel'),
            array($largeIconsWebFolder.'res_mspowerpoint.png', $defaultIcon, $completeIconMimeType, 'application/powerpoint'),
            array($largeIconsWebFolder.'res_msword.png', $defaultIcon, $completeIconMimeType, 'application/msword'),
            array($largeIconsWebFolder.'res_msword.png', $defaultIcon, $completeIconMimeType, 'application/vnd.oasis.opendocument.text'),
            array($largeIconsWebFolder.'res_pdf.png', $defaultIcon, $completeIconMimeType, 'application/pdf'),
            array($largeIconsWebFolder.'res_image.png', $defaultIcon, $basicIconMimeType, 'image'),
        );

        foreach ($resourceImages as $resourceImage) {
            $rimg = new ResourceIcon();
            $rimg->setIconLocation($resourceImage[0]);
            $rimg->setIconType($resourceImage[2]);
            $rimg->setType($resourceImage[3]);
            $manager->persist($rimg);
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