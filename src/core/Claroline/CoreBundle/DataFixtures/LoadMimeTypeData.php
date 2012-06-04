<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\Mime;

class LoadMimeTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //video
        $mp4 = new Mime();
        $mp4->setName("video/mp4");
        $manager->persist($mp4);
        
        $mov = new Mime();
        $mov->setName("video/mov");
        $manager->persist($mov);
        
        $flv = new Mime();
        $flv->setName("video/flv");
        $manager->persist($flv);
        
        //audio
        $ogg = new Mime();
        $ogg->setName("audio/ogg");
        $manager->persist($ogg);
        
        //application        
        $zip = new Mime();
        $zip->setName("application/zip");
        $manager->persist($zip);
        
        //images
        $pnj = new Mime();
        $pnj->setName("image/pnj");
        $manager->persist($pnj);
        
        $bmp = new Mime();
        $bmp->setName("image/bmp");
        $manager->persist($bmp);
        
        $jpg = new Mime();
        $jpg->setName("image/jpg");
        $manager->persist($jpg);
        
        $jpeg = new Mime();
        $jpeg->setName("image/jpeg");
        $manager->persist($jpeg);
        
        $text = new Mime();
        $text->setName("image/text");
        $manager->persist($text);
        
        $default = new Mime();
        $default->setName("claroline/default");
        $manager->persist($default);
        
        $manager->flush();
    }
    
    public function getOrder()
    {
        return 4;
    }
}
