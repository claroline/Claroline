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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;

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
        $coreIconWebDirRelativePath = 'bundles/clarolinecore/images/resources/icons/';
        $resourceImages = $this->getDefaultIconMap();

        foreach ($resourceImages as $resourceImage) {
            $rimg = new ResourceIcon();
            $rimg->setRelativeUrl($coreIconWebDirRelativePath.$resourceImage[0]);
            $rimg->setMimeType($resourceImage[1]);
            $rimg->setShortcut(false);
            $rimg->setUuid(uniqid('', true));
            $manager->persist($rimg);

            // Also add the new resource type icon to default resource icon set
            $this->container->get('claroline.manager.icon_set_manager')
                ->addOrUpdateIconItemToDefaultResourceIconSet($rimg);
        }
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getOrder()
    {
        return 3;
    }

    public function getDefaultIconMap()
    {
        return [
            ['res_default.png', 'custom/default'],
            ['res_file.png', 'custom/file'],
            ['res_folder.png', 'custom/directory'],
            ['res_text.png', 'text/plain'],
            ['res_text.png', 'custom/text'],

            //array('res_url.png', 'custom/url'),
            //array('res_exercice.png', 'custom/exercice'),
            ['res_jpeg.png', 'image'],
            ['res_audio.png', 'audio'],
            ['res_avi.png', 'video'],

            //images
            ['res_bmp.png', 'image/bmp'],
            ['res_bmp.png', 'image/x-windows-bmp'],
            ['res_jpeg.png', 'image/jpeg'],
            ['res_jpeg.png', 'image/pjpeg'],
            ['res_gif.png', 'image/gif'],
            ['res_tiff.png', 'image/tiff'],
            ['res_tiff.png', 'image/x-tiff'],

            //videos
            ['res_mp4.png', 'video/mp4'],
            ['res_mpeg.png', 'video/mpeg'],
            ['res_mpeg.png', 'audio/mpeg'],

            //sounds
            ['res_wav.png', 'audio/wav'],
            ['res_wav.png', 'audio/x-wav'],

            ['res_mp3.png', 'audio/mpeg3'],
            ['res_mp3.png', 'audio/x-mpeg3'],
            ['res_mp3.png', 'audio/mp3'],
            ['res_mp3.png', 'audio/mpeg'],

            //html
            ['res_html.png', 'text/html'],

            //xls
            ['res_xls.png', 'application/excel'],
            ['res_xls.png', 'application/vnd.ms-excel'],
            ['res_xls.png', 'application/msexcel'],
            ['res_xls.png', 'application/x-msexcel'],
            ['res_xls.png', 'application/x-ms-excel'],
            ['res_xls.png', 'application/x-excel'],
            ['res_xls.png', 'application/xls'],
            ['res_xls.png', 'application/x-xls'],
            ['res_xls.png', 'application/x-dos_ms_excel'],

            //xlsx
            ['res_xlsx.png', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],

            //odt
            ['res_odt.png', 'application/vnd.oasis.opendocument.text '],

            //ppt
            ['res_ppt.png', 'application/mspowerpoint'],
            ['res_ppt.png', 'application/powerpoint'],
            ['res_ppt.png', 'application/vnd.ms-powerpoint'],
            ['res_ppt.png', 'application/application/x-mspowerpoint'],

            //pptx
            ['res_pptx.png', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],

            //doc
            ['res_doc.png', 'application/msword'],

            //doc
            ['res_docx.png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],

            //pdf
            ['res_pdf.png', 'application/pdf'],

            //zip
            ['res_zip.png', 'application/zip'],
            ['res_rar.png', 'application/x-rar-compressed'],

            //rar
            ['res_archive.png', 'application/x-gtar'],
            ['res_archive.png', 'application/x-7z-compressed'],

            //gz
            ['res_gz.png', 'application/x-compressed'],
            ['res_gz.png', 'application/x-gzip'],
            ['res_gz.png', 'multipart/x-gzip'],

            //tar
            ['res_tar.png', 'application/x-tar'],

            //array('res_dot.png') alias for msword

            //odp
            ['res_odp.png', 'application/vnd.oasis.opendocument.presentation'],

            //ods
            ['res_ods.png', 'application/vnd.oasis.opendocument.spreadsheet'],

            //array('res_pps.png') alias for powerpoint
            //array('res_psp.png') couldn't find mime type

            ['res_rtf.png', 'application/rtf'],
            ['res_rtf.png', 'application/x-rtf'],
            ['res_rtf.png', 'text/richtext'],
        ];
    }
}
