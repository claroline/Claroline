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
        $iconsType = array('default', 'generated', 'basic_mime_type', 'complete_mime_type');
        $defaultIconType = null;

        foreach($iconsType as $type) {
            $iconType = new IconType();
            $iconType->setIconType($type);
            $manager->persist($iconType);
            if($type === 'default') {
                $defaultIconType = $iconType;
            }
        }


        $fileThumb = 'res_file.png';
        $folderThumb = 'res_folder.png';
        $textThumb = 'res_text.png';
        $defaultIcon = 'default_icon.img';

        $resourceImages = array(
            array($fileThumb, $defaultIcon),
            array($folderThumb, $defaultIcon),
            array($textThumb, $defaultIcon),
            array($fileThumb, $defaultIcon)
        );

        foreach ($resourceImages as $resourceImage) {
            $rimg = new ResourceIcon();
            $rimg->setThumbnail($resourceImage[0]);
            $rimg->setIcon($resourceImage[1]);
            $rimg->setIconType($defaultIconType);
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