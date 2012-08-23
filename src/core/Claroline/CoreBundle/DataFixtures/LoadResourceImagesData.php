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
        $iconsType = array('type', 'generated', 'basic_mime_type', 'complete_mime_type');
        $defaultIconType = null;
        $basicIconMimeType = null;
        $completeIconMimeType = null;

        foreach($iconsType as $type) {
            $iconType = new IconType();
            $iconType->setIconType($type);
            $manager->persist($iconType);
            switch($type) {
                case 'type':

                    $defaultIconType = $iconType; break;
                case 'basic_mime_type':
                    $basicIconMimeType = $iconType; break;
                case 'complete_mime_type':
                    $completeIconMimeType = $iconType; break;
                default:
                    break;
            }
        }


        $fileThumb = 'res_file.png';
        $folderThumb = 'res_folder.png';
        $textThumb = 'res_text.png';
        $defaultIcon = 'default_icon.img';
        $textPlainThumb = 'plain_text.png';

        /*
         * [1] thumbnail link
         * [2] icon link
         * [3] icontype
         * [4] type (either resource type or mime type-
         */
        $resourceImages = array(
            array($fileThumb, $defaultIcon, $defaultIconType, 'file'),
            array($folderThumb, $defaultIcon, $defaultIconType, 'directory'),
            array($textThumb, $defaultIcon, $defaultIconType, 'text'),
            array($fileThumb, $defaultIcon, $defaultIconType, 'default'),
            array($textPlainThumb, $defaultIcon, $completeIconMimeType, 'text/plain')
        );

        foreach ($resourceImages as $resourceImage) {
            $rimg = new ResourceIcon();
            $rimg->setThumbnail($resourceImage[0]);
            $rimg->setIcon($resourceImage[1]);
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