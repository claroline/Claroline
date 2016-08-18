<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBubdle\Tests\API;

use Claroline\CoreBundle\Library\Testing\Persister;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Library\Testing\CursusPersister;

/**
 * Specific tests for cursus
 * How to run:
 * - create database
 * - php app/console claroline:init_test_schema --env=test
 * - php app/console doctrine:schema:update --force --env=test
 * - SYMFONY_DEPRECATIONS_HELPER=weak bin/phpunit vendor/claroline/cursus-bundle/Tests/API/CursusControllerTest.php -c app/phpunit.xml.
 *
 ****************************************************************************************************
 *
 * Cursus structure :
 *
 *      root_cursus_A
 *          |
 *          |---- cursus_AA
 *          |         |
 *          |         |---- cursus_AAA (Course)
 *          |         |         * session_AAA_1 (SESSION_NOT_STARTED)
 *          |         |         * session_AAA_2 (SESSION_OPEN)
 *          |         |         * session_AAA_3 (SESSION_CLOSED)
 *          |         |
 *          |         |---- cursus_AAB (Course)
 *          |         |         * [NO SESSION]
 *          |         |
 *          |         |---- cursus_AAC (LOCKED)
 *          |                   |
 *          |                   |---- cursus_AACA (Course)
 *          |                             * session_AACA (SESSION_NOT_STARTED)
 *          |
 *          |---- cursus_AB (LOCKED)
 *          |         |
 *          |         |---- cursus_ABA (Course)
 *          |         |         * session_ABA_1 (SESSION_OPEN)
 *          |         |         * session_ABA_2 (SESSION_OPEN)
 *          |         |
 *          |         |---- cursus_ABB (Course)
 *          |                   * [NO SESSION]
 *          |
 *          |---- cursus_AC (Course)
 *                    * session_AC  (SESSION_CLOSED)
 *
 *
 *      root_cursus_B
 *          |
 *          |---- cursus_BA (Course)
 *          |         * session_BA_1  (SESSION_CLOSED)
 *          |         * session_BA_2 (SESSION_NOT_STARTED)
 *          |
 *          |---- cursus_BB (Course)
 *                    * [NO SESSION]
 */
class CursusControllerTest extends TransactionalTestCase
{
    /** @var Persister */
    private $persister;
    /** @var CursusPersister */
    private $cursusPersister;

    /** @var Cursus */
    private $rootCursusA;
    private $rootCursusB;
    private $cursusAA;
    private $cursusAB;
    private $cursusAC;
    private $cursusAAA;
    private $cursusAAB;
    private $cursusAAC;
    private $cursusAACA;
    private $cursusABA;
    private $cursusABB;
    private $cursusBA;
    private $cursusBB;

    /** @var Course */
    private $courseAAA;
    private $courseAAB;
    private $courseAACA;
    private $courseABA;
    private $courseABB;
    private $courseAC;
    private $courseBA;
    private $courseBB;

    /** @var CourseSession */
    private $sessionAAA1;
    private $sessionAAA2;
    private $sessionAAA3;
    private $sessionAACA;
    private $sessionABA1;
    private $sessionABA2;
    private $sessionAC;
    private $sessionBA1;
    private $sessionBA2;

    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
        $this->cursusPersister = $this->client->getContainer()->get('claroline.library.testing.cursuspersister');

        // Creates Courses
        $this->courseAAA = $this->cursusPersister->course('course_AAA');
        $this->courseAAB = $this->cursusPersister->course('course_AAB');
        $this->courseAACA = $this->cursusPersister->course('course_AACA');
        $this->courseABA = $this->cursusPersister->course('course_ABA');
        $this->courseABB = $this->cursusPersister->course('course_ABB');
        $this->courseAC = $this->cursusPersister->course('course_AC');
        $this->courseBA = $this->cursusPersister->course('course_BA');
        $this->courseBB = $this->cursusPersister->course('course_BB');

        // Creates Cursus
        $this->rootCursusB = $this->cursusPersister->cursus('root_cursus_B', null, null, 2);
        $this->rootCursusA = $this->cursusPersister->cursus('root_cursus_A', null, null, 1);
        $this->cursusAA = $this->cursusPersister->cursus('cursus_AA', $this->rootCursusA, null, 1);
        $this->cursusAB = $this->cursusPersister->cursus('cursus_AB', $this->rootCursusA, null, 2, true);
        $this->cursusAC = $this->cursusPersister->cursus('cursus_AC', $this->rootCursusA, $this->courseAC, 3);
        $this->cursusAAA = $this->cursusPersister->cursus('cursus_AAA', $this->cursusAA, $this->courseAAA, 1);
        $this->cursusAAB = $this->cursusPersister->cursus('cursus_AAB', $this->cursusAA, $this->courseAAB, 2);
        $this->cursusAAC = $this->cursusPersister->cursus('cursus_AAC', $this->cursusAA, null, 3, true);
        $this->cursusAACA = $this->cursusPersister->cursus('cursus_AACA', $this->cursusAAC, $this->courseAACA);
        $this->cursusABA = $this->cursusPersister->cursus('cursus_ABA', $this->cursusAB, $this->courseABA, 1);
        $this->cursusABB = $this->cursusPersister->cursus('cursus_ABB', $this->cursusAB, $this->courseABB, 2);
        $this->cursusBA = $this->cursusPersister->cursus('cursus_BA', $this->rootCursusB, $this->courseBA, 1);
        $this->cursusBB = $this->cursusPersister->cursus('cursus_BB', $this->rootCursusB, $this->courseBB, 2);

        // Creates Sessions
        $this->sessionAAA1 = $this->cursusPersister->session('session_AAA_1', $this->courseAAA, CourseSession::SESSION_NOT_STARTED);
        $this->sessionAAA2 = $this->cursusPersister->session('session_AAA_2', $this->courseAAA, CourseSession::SESSION_OPEN);
        $this->sessionAAA3 = $this->cursusPersister->session('session_AAA_3', $this->courseAAA, CourseSession::SESSION_CLOSED);
        $this->sessionAACA = $this->cursusPersister->session('session_AACA', $this->courseAACA, CourseSession::SESSION_NOT_STARTED);
        $this->sessionABA1 = $this->cursusPersister->session('session_ABA_1', $this->courseABA, CourseSession::SESSION_OPEN);
        $this->sessionABA2 = $this->cursusPersister->session('session_ABA_2', $this->courseABA, CourseSession::SESSION_OPEN);
        $this->sessionAC = $this->cursusPersister->session('session_AC', $this->courseAC, CourseSession::SESSION_CLOSED);
        $this->sessionBA1 = $this->cursusPersister->session('session_BA_1', $this->courseBA, CourseSession::SESSION_CLOSED);
        $this->sessionBA2 = $this->cursusPersister->session('session_BA_2', $this->courseBA, CourseSession::SESSION_NOT_STARTED);

        $this->persister->flush();
    }

    public function testGetAllRootCursusAction()
    {
        $this->markTestIncomplete('Unable to check for children. They are not fetched in the tests');
        $this->client->request('GET', '/clarolinecursusbundle/api/all/root/cursus.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $content = $this->client->getResponse()->getContent();
        $datas = json_decode($content, true);
        $this->assertEquals(2, count($datas));
        $this->assertEquals('root_cursus_A', $datas[0]['title']);
        $this->assertEquals('root_cursus_B', $datas[1]['title']);
    }

    public function testGetDatasForCursusRegistrationAction()
    {
        // Test with cursusAA
        $AAId = $this->cursusAA->getId();
        $this->client->request('GET', '/clarolinecursusbundle/api/datas/'.$AAId.'/for/cursus/registration.json');
    }

    public function testGetDatasForSearchedCursusRegistrationAction()
    {
        // Test with search : "B"
        $search = 'B';
        $this->client->request('GET', '/clarolinecursusbundle/api/datas/'.$search.'/for/searched/cursus/registration.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $content = $this->client->getResponse()->getContent();
        $datas = json_decode($content, true);
        $this->assertEquals(2, count($datas));
        $this->assertEquals(2, count($datas['roots']));
        $this->assertEquals(7, count($datas['searchedCursus']));
        $rootAId = $this->rootCursusA->getId();
        $rootBId = $this->rootCursusB->getId();
        $this->assertEquals($this->rootCursusA->getTitle(), $datas['roots'][$rootAId]['title']);
        $this->assertEquals($this->rootCursusB->getTitle(), $datas['roots'][$rootBId]['title']);

        $this->assertEquals($this->cursusAAB->getTitle(), $datas['searchedCursus'][0]['title']);
        $this->assertEquals($this->cursusAB->getTitle(), $datas['searchedCursus'][1]['title']);
        $this->assertEquals($this->cursusABA->getTitle(), $datas['searchedCursus'][2]['title']);
        $this->assertEquals($this->cursusABB->getTitle(), $datas['searchedCursus'][3]['title']);
        $this->assertEquals($this->cursusBA->getTitle(), $datas['searchedCursus'][4]['title']);
        $this->assertEquals($this->cursusBB->getTitle(), $datas['searchedCursus'][5]['title']);
        $this->assertEquals($this->rootCursusB->getTitle(), $datas['searchedCursus'][6]['title']);
    }

    public function testGetDatasForCursusHierarchyAction()
    {
        // Test for cursus_AA
        $cursusId = $this->cursusAA->getId();
        $this->client->request('GET', '/clarolinecursusbundle/api/datas/'.$cursusId.'/for/cursus/hierarchy.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $content = $this->client->getResponse()->getContent();
        $datas = json_decode($content, true);
        $AId = $this->rootCursusA->getId();
        $AAId = $this->cursusAA->getId();
        $AACId = $this->cursusAAC->getId();
        $this->assertEquals(4, count($datas));
        $this->assertEquals(1, count($datas['root']));
        $this->assertEquals(1, count($datas[$AId]));
        $this->assertEquals(3, count($datas[$AAId]));
        $this->assertEquals(1, count($datas[$AACId]));
        $this->assertEquals($this->rootCursusA->getTitle(), $datas['root'][0]['title']);
        $this->assertEquals($this->cursusAA->getTitle(), $datas[$AId][0]['title']);
        $this->assertEquals($this->cursusAAA->getTitle(), $datas[$AAId][0]['title']);
        $this->assertEquals($this->cursusAAB->getTitle(), $datas[$AAId][1]['title']);
        $this->assertEquals($this->cursusAAC->getTitle(), $datas[$AAId][2]['title']);
        $this->assertEquals($this->cursusAACA->getTitle(), $datas[$AACId][0]['title']);
    }

    public function testGetSessionsForCursusListAction()
    {
        // Test with a cursus without course
        $BId = $this->rootCursusB->getId();
        $this->client->request('GET', '/clarolinecursusbundle/api/sessions/'.$BId.'/for/cursus/list.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $content = $this->client->getResponse()->getContent();
        $datas = json_decode($content, true);
        $this->assertEquals(0, count($datas));

        // Test with 6 cursus & 3 courses
        $courseAAAId = $this->courseAAA->getId();
        $courseAABId = $this->courseAAB->getId();
        $courseAACAId = $this->courseAACA->getId();
        $ids = [];
        $ids[] = $this->rootCursusA->getId();
        $ids[] = $this->cursusAA->getId();
        $ids[] = $this->cursusAAA->getId();
        $ids[] = $this->cursusAAB->getId();
        $ids[] = $this->cursusAAC->getId();
        $ids[] = $this->cursusAACA->getId();
        $cursusIdsTxt = implode(',', $ids);
        $this->client->request('GET', '/clarolinecursusbundle/api/sessions/'.$cursusIdsTxt.'/for/cursus/list.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $content2 = $this->client->getResponse()->getContent();
        $datas2 = json_decode($content2, true);
        $this->assertEquals(3, count($datas2));
        $this->assertEquals($this->courseAAA->getTitle(), $datas2[$courseAAAId]['courseTitle']);
        $this->assertEquals($this->courseAAB->getTitle(), $datas2[$courseAABId]['courseTitle']);
        $this->assertEquals($this->courseAACA->getTitle(), $datas2[$courseAACAId]['courseTitle']);
        $this->assertEquals(2, count($datas2[$courseAAAId]['sessions']));
        $this->assertEquals(0, count($datas2[$courseAABId]['sessions']));
        $this->assertEquals(1, count($datas2[$courseAACAId]['sessions']));
        $this->assertEquals($this->sessionAACA->getName(), $datas2[$courseAACAId]['sessions'][0]['sessionName']);
        $this->assertNotEquals(CourseSession::SESSION_CLOSED, $datas2[$courseAAAId]['sessions'][0]['sessionStatus']);
        $this->assertNotEquals(CourseSession::SESSION_CLOSED, $datas2[$courseAAAId]['sessions'][1]['sessionStatus']);
    }
}
