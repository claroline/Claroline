<?php

namespace UJM\ExoBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\TypeOpenQuestion;
use UJM\ExoBundle\Entity\TypeQCM;


class LoadOptionsData extends AbstractFixture
{
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $valTqcm = array();
        
        $valTqcm[1] = 'Multiple response';
        $valTqcm[2] = 'Unique response';
        
        foreach ($valTqcm as $code => $val) {
            $this->newTQCM($val, $code);
        }

        $valTopen = array();
        $valTopen[1] = 'numerical';
        $valTopen[2] = 'long';
        $valTopen[3] = 'short';
        $valTopen[4] = 'oneWord';
        
        foreach ($valTopen as $code => $val) {
            $this->newTOPEN($val, $code);
        }
        
        $this->manager->flush();
    }

    private function newTQCM($val, $code)
    {
        $tqcm = new TypeQCM();
        $tqcm->setValue($val);
        $tqcm->setCode($code);

        $this->manager->persist($tqcm);
    }
    
    private function newTOPEN($val, $code)
    {
        $topen = new TypeOpenQuestion();
        $topen->setValue($val);
        $topen->setCode($code);

        $this->manager->persist($topen);
    }

}
