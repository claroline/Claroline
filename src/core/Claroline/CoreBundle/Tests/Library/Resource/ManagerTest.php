<?php

namespace Claroline\CoreBundle\Library\Resource;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class ManagerTest extends FixtureTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->manager = $this->client->getContainer()->get('claroline.resource.manager');
    }

    public function testCreationSetResourceAsLast()
    {
        $this->loadFileData('user', 'user', array('file1.txt'));
        $first = $this->getFile('file1.txt');
        $this->assertEquals(null, $first->getPrevious());
        $this->assertEquals(null, $first->getNext());
        $this->loadFileData('user', 'user', array('file2.txt'));
        $second = $this->getFile('file2.txt');
        $this->assertEquals(null, $first->getPrevious());
        $this->assertEquals($second, $first->getNext());
        $this->assertEquals($first, $second->getPrevious());
        $this->loadFileData('user', 'user', array('file3.txt'));
        $third = $this->getFile('file3.txt');
        $this->assertEquals($third, $second->getNext());
        $this->assertEquals($first, $second->getPrevious());
    }

    public function testCreateSetShortcutAsLast()
    {
        $this->loadFileData('admin', 'admin', array('target.txt'));
        $this->loadShortcutData(
            $this->getFile('target.txt'),
            'user',
            'user',
            'first'
        );

        $first = $this->getShortcut('first');
        $this->assertEquals(null, $first->getPrevious());
        $this->assertEquals(null, $first->getNext());
        $this->loadShortcutData(
            $this->getFile('target.txt'),
            'user',
            'user',
            'second'
        );

        $second = $this->getShortcut('second');
        $this->assertEquals(null, $first->getPrevious());
        $this->assertEquals($second, $first->getNext());
        $this->assertEquals($first, $second->getPrevious());

        $this->loadShortcutData(
            $this->getFile('target.txt'),
            'user',
            'user',
            'third'
        );

        $third = $this->getShortcut('third');
        $this->assertEquals($third, $second->getNext());
        $this->assertEquals($first, $second->getPrevious());
    }

    public function testDeleteSetPosition()
    {
        $this->loadFileData('user', 'user', array('file1.txt'));
        $this->loadFileData('user', 'user', array('file2.txt'));
        $this->loadFileData('user', 'user', array('file3.txt'));
        $this->loadFileData('user', 'user', array('file4.txt'));
        $first = $this->getFile('file1.txt');
        $second = $this->getFile('file2.txt');
        $third = $this->getFile('file3.txt');
        $fourth = $this->getFile('file4.txt');
        $this->manager->delete($second);
        $this->assertEquals($first, $third->getPrevious());
        $this->assertEquals($third, $first->getNext());
        $this->assertEquals(null, $first->getPrevious());
        $this->assertEquals($fourth, $third->getNext());
        $this->manager->delete($fourth);
        $this->assertEquals(null, $third->getNext());
        $this->assertEquals($first, $third->getPrevious());
        $this->assertEquals($third, $first->getNext());
        $this->assertEquals(null, $first->getPrevious());
        $this->manager->delete($first);
        $this->assertEquals(null, $third->getPrevious());
        $this->assertEquals(null, $third->getNext());
    }

    public function testMoveSetPosition()
    {
        $this->loadFileData('user', 'user', array('file1.txt'));
        $this->loadFileData('user', 'user', array('file2.txt'));
        $this->loadDirectoryData('user', array('user/newParent'));
        $this->loadFileData('user', 'newParent', array('file3.txt'));
        $parentDir = $this->getDirectory('newParent');
        $first = $this->getFile('file1.txt');
        $second = $this->getFile('file2.txt');
        $third = $this->getFile('file3.txt');
        $this->manager->move($first, $this->getDirectory('newParent'));
        $this->assertEquals($parentDir, $second->getNext());
        $this->assertEquals(null, $second->getPrevious());
        $this->assertEquals(null, $first->getNext());
        $this->assertEquals($third->getName(), $first->getPrevious()->getName());
    }

    public function testCopySetPosition()
    {
        $this->loadFileData('user', 'user', array('file1.txt'));
        $first = $this->getFile('file1.txt');
        $dir = $this->getDirectory('user');
        $copy = $this->manager->copy($first, $dir, $this->getUser('user'));
        $this->assertEquals($first, $copy->getPrevious());
        $this->assertEquals(null, $copy->getNext());
    }

    public function testInsertBefore()
    {
        $this->loadFileData('user', 'user', array('file1.txt'));
        $this->loadFileData('user', 'user', array('file2.txt'));
        $this->loadFileData('user', 'user', array('file3.txt'));
        $this->loadFileData('user', 'user', array('file4.txt'));
        $first = $this->getFile('file1.txt');
        $second = $this->getFile('file2.txt');
        $third = $this->getFile('file3.txt');
        $fourth = $this->getFile('file4.txt');
        $this->manager->insertBefore($first, $third);
        $this->assertEquals($first, $second->getNext());
        $this->assertEquals($first, $third->getPrevious());
        $this->assertEquals($second, $first->getPrevious());
        $this->assertEquals($third, $first->getNext());
        $this->assertEquals(null, $second->getPrevious());
        $this->manager->insertBefore($first);
        $this->assertEquals($first, $fourth->getNext());
        $this->assertEquals($fourth, $first->getPrevious());
        $this->assertEquals(null, $first->getNext());
        $this->manager->insertBefore($first, $second);
        $this->assertEquals(null, $first->getPrevious());
        $this->assertEquals($second, $first->getNext());
        $this->assertEquals($first, $second->getPrevious());
    }

    public function testIsPathValid()
    {

    }
}
