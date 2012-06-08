<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\MimeType;

class LoadMimeTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //video
        $mp4 = new MimeType();
        $mp4->setName("video/mp4");
        $manager->persist($mp4);
        
        $mov = new MimeType();
        $mov->setName("video/mov");
        $manager->persist($mov);
        
        $flv = new MimeType();
        $flv->setName("video/flv");
        $manager->persist($flv);
        
        //audio
        $ogg = new MimeType();
        $ogg->setName("audio/ogg");
        $manager->persist($ogg);
        
        //application        
        $zip = new MimeType();
        $zip->setName("application/zip");
        $manager->persist($zip);
        
        //images
        $pnj = new MimeType();
        $pnj->setName("image/pnj");
        $manager->persist($pnj);
        
        $bmp = new MimeType();
        $bmp->setName("image/bmp");
        $manager->persist($bmp);
        
        $jpg = new MimeType();
        $jpg->setName("image/jpg");
        $manager->persist($jpg);
        
        $jpeg = new MimeType();
        $jpeg->setName("image/jpeg");
        $manager->persist($jpeg);
        
        $text = new MimeType();
        $text->setName("image/text");
        $manager->persist($text);
        
        $default = new MimeType();
        $default->setName("claroline/default");
        $manager->persist($default);
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 4;
    }
}
