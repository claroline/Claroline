<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required\Data;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;

/**
 * Resource images data fixture.
 */
class LoadResourceImagesData implements RequiredFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $coreIconWebDirRelativePath = "bundles/clarolinecore/images/resources/icons/";
        $resourceImages = array(
            array('res_default.png', 'custom/default'),
            array('res_default.png', 'custom/activity'),
            array('res_file.png', 'custom/file'),
            array('res_folder.png', 'custom/directory'),
            array('res_text.png', 'text/plain'),
            array('res_text.png', 'custom/text'),
            array('res_url.png', 'custom/url'),
            array('res_exercice.png', 'custom/exercice'),
            array('res_audio.png', 'audio'),
            array('res_video.png', 'video'),
            array('res_msexcel.png', 'application/excel'),
            array('res_mspowerpoint.png', 'application/powerpoint'),
            array('res_msword.png', 'application/msword'),
            array('res_msword.png', 'application/vnd.oasis.opendocument.text'),
            array('res_pdf.png', 'application/pdf'),
            array('res_image.png', 'image'),
            array('res_vector.png', 'application/postscript'),
            array('res_vector.png', 'image/svg+xml'),
            array('res_zip.png', 'application/x-gtar'),
            array('res_zip.png', 'application/x-7z-compressed'),
            array('res_zip.png', 'application/x-rar-compressed')
        );

        foreach ($resourceImages as $resourceImage) {
            $rimg = new ResourceIcon();
            $rimg->setRelativeUrl($coreIconWebDirRelativePath . $resourceImage[0]);
            $rimg->setMimeType($resourceImage[1]);
            $rimg->setShortcut(false);
            $manager->persist($rimg);

            $this->container->get('claroline.manager.icon_manager')
                ->createShortcutIcon($rimg);
        }
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
