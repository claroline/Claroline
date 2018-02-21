<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\ResultBundle\Entity\Result;
use Claroline\ResultBundle\Testing\Persister;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ResultManagerTest extends TransactionalTestCase
{
    /** @var ResultManager */
    private $manager;
    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;

    protected function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->manager = $container->get('claroline.result.result_manager');
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->persist = new Persister($this->om, $container);
    }

    public function testCreateAndDelete()
    {
        $repo = $this->om->getRepository('ClarolineResultBundle:Result');
        $result = $this->manager->create(new Result(null, 20));
        $results = $repo->findAll();
        $this->assertEquals(1, count($results));
        $this->assertEquals($result, $results[0]);
        $this->manager->delete($results[0]);
        $this->assertEquals(0, count($repo->findAll()));
    }

    public function testWidget()
    {
        $bob = $this->persist->user('bob');
        $this->om->flush();
        $content = $this->manager->getWidgetContent($bob->getPersonalWorkspace(), $bob);
        $this->assertNotEmpty($content);
    }

    public function testGetMarksWithAndWithoutFullAccess()
    {
        $bob = $this->persist->user('bob');
        $bill = $this->persist->user('bill');
        $jane = $this->persist->user('jane');

        $result = $this->persist->result('eval 1', $bob);

        $billMark = $this->persist->mark($result, $bill, 12);
        $janeMark = $this->persist->mark($result, $jane, 14);

        $this->om->flush();

        $expected = [
            [
                'id' => $bill->getId(),
                'name' => 'bill bill',
                'mark' => 12,
                'markId' => $billMark->getId(),
            ],
            [
                'id' => $jane->getId(),
                'name' => 'jane jane',
                'mark' => 14,
                'markId' => $janeMark->getId(),
            ],
        ];
        $actual = $this->manager->getMarks($result, $bob, true);
        $this->assertEquals($expected, $actual);
    }

    public function testImportExpectsNonEmptyFile()
    {
        $john = $this->persist->user('john');
        $result = $this->persist->result('Result 1', $john);
        $this->persist->workspaceUser($john->getPersonalWorkspace(), $john);
        $this->om->flush();

        $data = $this->manager->importMarksFromCsv($result, $this->stubCsv('empty'));
        $this->assertEquals(1, count($data['errors']));
        $this->assertEquals(ResultManager::ERROR_EMPTY_CSV, $data['errors'][0]['code']);
    }

    public function testImportExpectsThreeValues()
    {
        $john = $this->persist->user('john');
        $result = $this->persist->result('Result 1', $john);
        $this->persist->workspaceUser($john->getPersonalWorkspace(), $john);
        $this->om->flush();

        $data = $this->manager->importMarksFromCsv($result, $this->stubCsv('missing-values'));
        $this->assertEquals(2, count($data['errors']));
        $this->assertEquals(ResultManager::ERROR_MISSING_VALUES, $data['errors'][0]['code']);
        $this->assertEquals(1, $data['errors'][0]['line']);
        $this->assertEquals(ResultManager::ERROR_MISSING_VALUES, $data['errors'][1]['code']);
        $this->assertEquals(3, $data['errors'][1]['line']);
    }

    public function testImportExpectsNonEmptyValues()
    {
        $john = $this->persist->user('john');
        $result = $this->persist->result('Result 1', $john);
        $this->om->flush();

        $data = $this->manager->importMarksFromCsv($result, $this->stubCsv('empty-values'));
        $this->assertEquals(1, count($data['errors']));
        $this->assertEquals(ResultManager::ERROR_EMPTY_VALUES, $data['errors'][0]['code']);
        $this->assertEquals(1, $data['errors'][0]['line']);
    }

    public function testImportExpectsValidMarks()
    {
        $john = $this->persist->user('john');
        $this->persist->workspaceUser($john->getPersonalWorkspace(), $john);
        $result = $this->persist->result('Result 1', $john);
        $this->om->flush();

        $data = $this->manager->importMarksFromCsv($result, $this->stubCsv('invalid-marks'));
        $this->assertEquals(2, count($data['errors']));
        $this->assertEquals(ResultManager::ERROR_INVALID_MARK, $data['errors'][0]['code']);
        $this->assertEquals(1, $data['errors'][0]['line']);
        $this->assertEquals(ResultManager::ERROR_INVALID_MARK, $data['errors'][1]['code']);
        $this->assertEquals(3, $data['errors'][1]['line']);
    }

    public function testImportExpectsWorkspaceUsers()
    {
        $john = $this->persist->user('john');
        $bob = $this->persist->user('bob');
        $this->persist->workspaceUser($john->getPersonalWorkspace(), $john);
        $this->persist->workspaceUser($john->getPersonalWorkspace(), $bob);
        $result = $this->persist->result('Result 1', $john);
        $this->om->flush();

        $data = $this->manager->importMarksFromCsv($result, $this->stubCsv('valid-1'));
        $this->assertEquals(1, count($data['errors']));
        $this->assertEquals(ResultManager::ERROR_EXTRA_USERS, $data['errors'][0]['code']);
        $this->assertEquals(2, $data['errors'][0]['line']);
    }

    public function testImportMarks()
    {
        $john = $this->persist->user('john');
        $jane = $this->persist->user('jane');
        $bob = $this->persist->user('bob');
        $this->persist->workspaceUser($john->getPersonalWorkspace(), $john);
        $this->persist->workspaceUser($john->getPersonalWorkspace(), $jane);
        $this->persist->workspaceUser($john->getPersonalWorkspace(), $bob);
        $result = $this->persist->result('Result 1', $john);
        $this->om->flush();

        $data = $this->manager->importMarksFromCsv($result, $this->stubCsv('valid-2'));
        $this->assertEquals(0, count($data['errors']));
        $this->assertEquals(3, count($data['marks']));

        $marks = $this->om->getRepository('ClarolineResultBundle:Mark')->findAll();
        $this->assertEquals(3, count($marks));
        $this->assertEquals($data['marks'], $marks);
    }

    private function stubCsv($name, $extension = '.csv')
    {
        $path = __DIR__.'/../Stub/csv/'.$name.$extension;
        $file = new UploadedFile($path, $name);

        return $file;
    }
}
