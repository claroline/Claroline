<?php
/**
 * Created by PhpStorm.
 * User: Aurelien
 * Date: 25/09/14
 * Time: 10:19.
 */

namespace Innova\CollecticielBundle\Tests\Entity;

use Innova\CollecticielBundle\Entity\Correction;

class DropTest extends \PHPUnit_Framework_TestCase
{
    public function testgetCalculatedGrade()
    {
        $correction1 = $this->createCorrection(10);
        $correction2 = $this->createCorrection(20);
        $correction3 = $this->createCorrection(5);
        $correction4 = $this->createCorrection(3, false);
        $correction5 = $this->createCorrection(12, false, false);
    }

    private function createCorrection($grade = null, $valid = true, $finished = true)
    {
        $correction = new Correction();
        $correction->setFinished($finished);
        $correction->setValid($valid);
        $correction->setTotalGrade($grade);
    }
}
