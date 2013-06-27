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
        $iconsType = array(
            'type',
            'generated',
            'basic_mime_type',
            'complete_mime_type',
            'default',
            'custom'
        );
        $defaultIconType = null;
        $basicIconMimeType = null;
        $completeIconMimeType = null;

        foreach ($iconsType as $type) {
            $iconType = new IconType();
            $iconType->setType($type);
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
        $coreWebDir = "{$this->container->getParameter('kernel.root_dir')}{$ds}..{$ds}web{$ds}";
        $coreIconWebDirRelativePath = "bundles/clarolinecore/images/resources/icons/";
        $coreIconWebDir = "{$coreWebDir}bundles{$ds}clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}";
        $resourceImages = array(
            array('res_default.png', $defaultIconType, 'default'),
            array('res_file.png', $typeIconType, 'file'),
            array('res_folder.png', $typeIconType, 'directory'),
            array('res_text.png', $completeIconMimeType, 'text/plain'),
            array('res_text.png', $basicIconMimeType, 'text'),
            array('res_text.png', $typeIconType, 'text'),
            array('res_url.png', $typeIconType, 'url'),
            array('res_exercice.png', $typeIconType, 'exercice'),
            array('res_video.png', $basicIconMimeType, 'video'),
            array('res_msexcel.png', $completeIconMimeType, 'application/excel'),
            array('res_mspowerpoint.png', $completeIconMimeType, 'application/powerpoint'),
            array('res_msword.png', $completeIconMimeType, 'application/msword'),
            array('res_msword.png', $completeIconMimeType, 'application/vnd.oasis.opendocument.text'),
            array('res_pdf.png', $completeIconMimeType, 'application/pdf'),
            array('res_image.png', $basicIconMimeType, 'image'),
        );

        foreach ($resourceImages as $resourceImage) {
            $rimg = new ResourceIcon();
            $rimg->setIconLocation($coreIconWebDir . $resourceImage[0]);
            $rimg->setRelativeUrl($coreIconWebDirRelativePath . $resourceImage[0]);
            $rimg->setIconType($resourceImage[1]);
            $rimg->setType($resourceImage[2]);
            $rimg->setShortcut(false);
            $manager->persist($rimg);

            $this->container->get('claroline.resource.icon_creator')
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