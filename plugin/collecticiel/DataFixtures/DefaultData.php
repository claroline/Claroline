<?php

namespace Innova\CollecticielBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\CollecticielBundle\Entity\ReturnReceiptType;

class DefaultData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        /* RETURN RECEIPT TYPE ARRAY */
        /*
         * array format:
         *   - name
         */
        $returnreceipttypesArray = array(
                array('NO RETURN RECEIPT'),
                array('DOUBLOON'),
                array('DOCUMENT RECEIVED'),
                array('DOCUMENT UNREADABLE'),
                array('INCOMPLETE DOCUMENT'),
                array('ERROR DOCUMENT'),
        );

        /* TRAITEMENT */
        foreach ($returnreceipttypesArray as $returnreceipttype) {
            $returnReceiptTypeAdd = new ReturnReceiptType();
            $returnReceiptTypeAdd->setTypeName($returnreceipttype[0]);
            $manager->persist($returnReceiptTypeAdd);
        }

        $manager->flush();
    }
}
