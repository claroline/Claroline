<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceImage;

/**
 * Resource types data fixture.
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
        $fileThumb ='res_file.png';
        $folderThumb ='res_folder.png';
        $textThumb ='res_text.png';

        $resourceImages = array(
            array('file', $fileThumb),
            array('directory', $folderThumb),
            array('text', $textThumb),
            array('default', $fileThumb)
        );

        foreach ($resourceImages as $resourceImage) {
            $rimg = new ResourceImage();
            $rimg->setType($resourceImage[0]);
            $rimg->setThumbnail($resourceImage[1]);
            $manager->persist($rimg);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 5;
    }
}