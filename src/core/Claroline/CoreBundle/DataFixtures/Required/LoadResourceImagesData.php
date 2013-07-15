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
        $ds = DIRECTORY_SEPARATOR;
        $coreWebDir = "{$this->container->getParameter('kernel.root_dir')}{$ds}..{$ds}web{$ds}";
        $coreIconWebDirRelativePath = "bundles/clarolinecore/images/resources/icons/";
        $coreIconWebDir = "{$coreWebDir}bundles{$ds}clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}";
        $resourceImages = array(
            array('res_default.png', 'custom/default'),
            array('res_default.png', 'custom/activity'),
            array('res_file.png', 'custom/file'),
            array('res_folder.png', 'custom/directory'),
            array('res_text.png', 'text/plain'),
            array('res_text.png', 'custom/text'),
            array('res_url.png', 'custom/url'),
            array('res_exercice.png', 'custom/exercice'),
            array('res_video.png', 'video'),
            array('res_msexcel.png', 'application/excel'),
            array('res_mspowerpoint.png', 'application/powerpoint'),
            array('res_msword.png', 'application/msword'),
            array('res_msword.png', 'application/vnd.oasis.opendocument.text'),
            array('res_pdf.png', 'application/pdf'),
            array('res_image.png', 'image'),
        );

        foreach ($resourceImages as $resourceImage) {
            $rimg = new ResourceIcon();
            $rimg->setIconLocation($coreIconWebDir . $resourceImage[0]);
            $rimg->setRelativeUrl($coreIconWebDirRelativePath . $resourceImage[0]);
            $rimg->setMimeType($resourceImage[1]);
            $rimg->setShortcut(false);
            $manager->persist($rimg);

            $this->container->get('claroline.manager.icon_manager')
                ->createShortcutIcon($rimg);
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