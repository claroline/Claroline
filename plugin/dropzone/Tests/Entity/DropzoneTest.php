<?php
/**
 * Created by PhpStorm.
 * User: Aurelien
 * Date: 29/09/14
 * Time: 11:24.
 */

namespace Icap\DropzoneBundle\Tests\Entity;

use Icap\DropzoneBundle\Entity\Dropzone;
use DateTime;

class DropzoneTest extends \PHPUnit_Framework_TestCase
{
    private function createFixturesDropzones()
    {
        $dz0 = new Dropzone();
        $dz0->setManualState(Dropzone::MANUAL_STATE_NOT_STARTED);
        $dz0->setManualPlanning(true);
        $dz0->setPeerReview(true);

        $dz1 = new Dropzone();
        $dz1->setManualState(Dropzone::MANUAL_STATE_ALLOW_DROP);
        $dz1->setManualPlanning(true);
        $dz1->setPeerReview(true);

        $dz2 = new Dropzone();
        $dz2->setManualState(Dropzone::MANUAL_STATE_ALLOW_DROP_AND_PEER_REVIEW);
        $dz2->setManualPlanning(true);
        $dz2->setPeerReview(true);

        $dz3 = new Dropzone();
        $dz3->setManualState(Dropzone::MANUAL_STATE_FINISHED);
        $dz3->setManualPlanning(true);
        $dz3->setPeerReview(true);

        $dz4 = new Dropzone();
        $dz4->setManualState(Dropzone::MANUAL_STATE_PEER_REVIEW);
        $dz4->setManualPlanning(true);
        $dz4->setPeerReview(true);

        $dz5 = new Dropzone();
        $dz5->setManualPlanning(false);
        $dz5->setStartAllowDrop(new DateTime());
        $dateEnd6 = DateTime::createFromFormat('d-m-Y', '15-02-2019');
        $dz5->setEndAllowDrop($dateEnd6);
        $dz5->setPeerReview(true);

        $dz6 = new Dropzone();
        $dz6->setManualPlanning(false);
        $date7 = DateTime::createFromFormat('d-m-Y', '15-02-2010');
        $dateEnd7 = DateTime::createFromFormat('d-m-Y', '15-02-2011');
        $dz6->setStartAllowDrop($date7);
        $dz6->setEndAllowDrop($dateEnd7);
        $dz6->setPeerReview(true);

        $dz7 = new Dropzone();
        $dz7->setManualPlanning(false);
        $date8 = DateTime::createFromFormat('d-m-Y', '15-02-2019');
        $dateEnd8 = DateTime::createFromFormat('d-m-Y', '15-02-2020');
        $dz7->setStartAllowDrop($date8);
        $dz7->setEndAllowDrop($dateEnd8);
        $dz7->setPeerReview(true);

        return array($dz0, $dz1, $dz2, $dz3, $dz4, $dz5, $dz6, $dz7);
    }

    public function testisNotStarted()
    {
        $dzs = $this->createFixturesDropzones();
        $this->assertEquals(true, $dzs[0]->isNotStarted());
        $this->assertEquals(false, $dzs[1]->isNotStarted());
        $this->assertEquals(false, $dzs[2]->isNotStarted());
        $this->assertEquals(false, $dzs[3]->isNotStarted());
        $this->assertEquals(false, $dzs[4]->isNotStarted());
        $this->assertEquals(false, $dzs[5]->isNotStarted());
        $this->assertEquals(false, $dzs[6]->isNotStarted());
        $this->assertEquals(true, $dzs[7]->isNotStarted());
    }

    public function testisAllowDrop()
    {
        $dzs = $this->createFixturesDropzones();
        $this->assertEquals(false, $dzs[0]->isAllowDrop());
        $this->assertEquals(true, $dzs[1]->isAllowDrop());
        $this->assertEquals(true, $dzs[2]->isAllowDrop());
        $this->assertEquals(false, $dzs[3]->isAllowDrop());
        $this->assertEquals(false, $dzs[4]->isAllowDrop());
        $this->assertEquals(true, $dzs[5]->isAllowDrop());
        $this->assertEquals(false, $dzs[6]->isAllowDrop());
        $this->assertEquals(false, $dzs[7]->isAllowDrop());
    }

    public function testIsPeerReview()
    {
        $dzs = $this->createFixturesDropzones();
        $this->assertEquals(false, $dzs[0]->isPeerReview());
        $this->assertEquals(false, $dzs[1]->isPeerReview());
        $this->assertEquals(true, $dzs[2]->isPeerReview());
        $this->assertEquals(false, $dzs[3]->isPeerReview());
        $this->assertEquals(true, $dzs[4]->isPeerReview());
        $this->assertEquals(false, $dzs[5]->isPeerReview());
        $this->assertEquals(false, $dzs[6]->isPeerReview());
        $this->assertEquals(false, $dzs[7]->isPeerReview());

        $dz2 = new Dropzone();
        $dz2->setManualState(Dropzone::MANUAL_STATE_ALLOW_DROP_AND_PEER_REVIEW);
        $dz2->setManualPlanning(true);
        $dz2->setPeerReview(false);

        $this->assertEquals(false, $dz2->isPeerReview());
    }

    public function testIsFinished()
    {
        $dzs = $this->createFixturesDropzones();

        $this->assertEquals(false, $dzs[0]->isFinished());
        $this->assertEquals(false, $dzs[1]->isFinished());
        $this->assertEquals(false, $dzs[2]->isFinished());
        $this->assertEquals(true, $dzs[3]->isFinished());
        $this->assertEquals(false, $dzs[4]->isFinished());
        $this->assertEquals(false, $dzs[5]->isFinished());
        $this->assertEquals(true, $dzs[6]->isFinished());
        $this->assertEquals(false, $dzs[7]->isFinished());
    }
}
