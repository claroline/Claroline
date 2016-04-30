<?php
/**
 * Created by PhpStorm.
 * User: Aurelien
 * Date: 25/09/14
 * Time: 10:19.
 */

namespace Icap\DropzoneBundle\Tests\Entity;

use Icap\DropzoneBundle\Entity\Correction;
use Icap\DropzoneBundle\Entity\Drop;
use DateTime;

class DropTest extends \PHPUnit_Framework_TestCase
{
    public function testgetCalculatedGrade()
    {
        $correction1 = $this->createCorrection(10);
        $correction2 = $this->createCorrection(20);
        $correction3 = $this->createCorrection(5);
        $correction4 = $this->createCorrection(3, false);
        $correction5 = $this->createCorrection(12, false, false);

        $drop1 = new Drop();
        $drop1->setCorrections(array($correction1, $correction2));
        $this->assertEquals(15, $drop1->getCalculatedGrade());

        $drop2 = new Drop();
        $drop2->setCorrections(array($correction2, $correction3, $correction4, $correction5));
        $this->assertEquals(12.5, $drop2->getCalculatedGrade());

        $drop3 = new Drop();
        $drop3->setCorrections(array($correction4, $correction5));
        $this->assertEquals(-1, $drop3->getCalculatedGrade());
    }

    private function createCorrection($grade = null, $valid = true, $finished = true)
    {
        $correction = new Correction();
        $correction->setFinished($finished);
        $correction->setValid($valid);
        $correction->setTotalGrade($grade);

        return $correction;
    }

    public function testCountFinishedCorrections()
    {
        // WARN, countFinished correction care about the valid property
        // a finished correction but invalis should not be in the count.

        $correction1 = $this->createCorrection(10, true);
        $correction2 = $this->createCorrection(20, false, true);
        $correction3 = $this->createCorrection(5, true, false);
        $correction4 = $this->createCorrection(3, false);
        $correction5 = $this->createCorrection(12, false, false);
        $correction6 = $this->createCorrection(12, true, true);

        $drop1 = new Drop();
        $drop1->setCorrections(array($correction1, $correction2));
        $this->assertEquals(1, $drop1->countFinishedCorrections());

        $drop2 = new Drop();
        $drop2->setCorrections(array($correction2, $correction3, $correction4, $correction5));
        $this->assertEquals(0, $drop2->countFinishedCorrections());

        $drop3 = new Drop();
        $drop3->setCorrections(array($correction1, $correction3));
        $this->assertEquals(1, $drop3->countFinishedCorrections());

        $drop4 = new Drop();
        $drop4->setCorrections(array($correction1, $correction2, $correction4, $correction5, $correction3, $correction6));
        $this->assertEquals(2, $drop4->countFinishedCorrections());
    }

    public function testGetHasDeniedCorrection()
    {
        $correction1 = $this->createCorrection(10, true);
        $correction1->setCorrectionDenied(true);
        $correction2 = $this->createCorrection(20, false, true);
        $correction2->setCorrectionDenied(true);
        $correction3 = $this->createCorrection(5, true, false);
        $correction3->setCorrectionDenied(false);
        $correction4 = $this->createCorrection(3, false);
        $correction5 = $this->createCorrection(12, false, false);
        $correction6 = $this->createCorrection(12, true, true);

        $drop1 = new Drop();
        $drop1->setCorrections(array($correction1, $correction2));
        $this->assertEquals(true, $drop1->getHasDeniedCorrection());

        $drop2 = new Drop();
        $drop2->setCorrections(array($correction2, $correction3, $correction4, $correction5));
        $this->assertEquals(true, $drop2->getHasDeniedCorrection());

        $drop3 = new Drop();
        $drop3->setCorrections(array($correction4, $correction3));
        $this->assertEquals(false, $drop3->getHasDeniedCorrection());

        $drop4 = new Drop();
        $drop4->setCorrections(array($correction1, $correction2, $correction4, $correction5, $correction3, $correction6));
        $this->assertEquals(true, $drop4->getHasDeniedCorrection());
    }

    public function testGetLastCorrectionDate()
    {
        $date1 = DateTime::createFromFormat('d-m-Y', '15-02-2009');
        $correction1 = $this->createCorrection(10, true);
        $correction1->setEndDate($date1);

        $date2 = DateTime::createFromFormat('d-m-Y', '15-02-2010');
        $correction2 = $this->createCorrection(20, false, true);
        $correction2->setEndDate($date2);

        $date3 = DateTime::createFromFormat('d-m-Y', '15-05-2010');
        $correction3 = $this->createCorrection(5, true, false);
        $correction3->setEndDate($date3);

        $date4 = DateTime::createFromFormat('d-m-Y', '16-05-2014');
        $correction4 = $this->createCorrection(3, false);
        $correction4->setEndDate($date4);

        $drop1 = new Drop();
        $drop1->setCorrections(array($correction1, $correction2));
        $this->assertEquals($date2, $drop1->getLastCorrectionDate());

        $drop2 = new Drop();
        $drop2->setCorrections(array($correction2, $correction3, $correction4));
        $this->assertEquals($date4, $drop2->getLastCorrectionDate());

        $drop3 = new Drop();
        $drop3->setCorrections(array($correction4, $correction3));
        $this->assertEquals($date4, $drop3->getLastCorrectionDate());
    }
}
