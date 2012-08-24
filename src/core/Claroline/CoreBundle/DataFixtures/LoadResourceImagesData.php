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

        $defaultIcon = 'res_default.png';
        /*
         * [1] thumbnail link
         * [2] icon link
         * [3] icontype
         * [4] type (either resource type or mime type-
         */
        $resourceImages = array(
            // Types owned and managed by the Claroline platform
            array('res_default.png', $defaultIcon, $defaultIconType, 'default'),
            array('res_folder.png', $defaultIcon, $defaultIconType, 'directory'),
            array('res_file.png', $defaultIcon, $defaultIconType, 'file'),
            array('res_text.png', $defaultIcon, $defaultIconType, 'text'),
            array('res_url.png', $defaultIcon, $defaultIconType, 'url'),
            array('res_exercice.png', $defaultIcon, $defaultIconType, 'exercice'),
            array('res_forum.png', $defaultIcon, $defaultIconType, 'forum'),
            // Types linked to a (set of) mimetype(s)
            array('res_text.png', $defaultIcon, $completeIconMimeType, 'text/plain'),
            array('res_pdf.png', $defaultIcon, $completeIconMimeType, 'application/pdf'),
            array('res_msexcel.png', $defaultIcon, $completeIconMimeType, 'application/vnd.ms-excel'),
            array('res_mspowerpoint.png', $defaultIcon, $completeIconMimeType, 'application/vnd.ms-powerpoint'),
            array('res_msword.png', $defaultIcon, $completeIconMimeType, 'application/msword'),
            array('res_video.png', $defaultIcon, $basicIconMimeType, 'video') // = video/*
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