<?php

namespace Claroline\CoreBundle\Writer;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class MessageWriterTest extends FixtureTestCase
{
    /** @var MessageWriter */
    private $writer;
    private $msgRepo;
    private $usrMsgRepo;

    public function setUp()
    {
        parent::setup();
        $this->writer = $this->client->getContainer()->get('claroline.writer.message_writer');
        $this->msgRepo = $this->em->getRepository('ClarolineCoreBundle:Message');
        $this->usrMsgRepo = $this->em->getRepository('ClarolineCoreBundle:UserMessage');
        $this->loadPlatformRoleData();
        $this->loadUserData(array('sender' => 'user', 'receiver' => 'user'), false);
        $this->loadMessagesData(array(array('from' => 'sender', 'to' => 'receiver', 'object' => 'object')));
    }

    public function testCreate()
    {
        $this->writer->create(
            $this->getUser('sender'),
            $this->getUser('receiver')->getUsername(),
            array($this->getUser('receiver')),
            'content',
            'object'
        );

        $object = $this->msgRepo->findOneByObject('object');

        $this->assertEquals('object', $object->getObject());
    }

    public function testMarkAsRead()
    {
        $um = $this->usrMsgRepo->findAll();
        $this->writer->markAsRead($um[0]);
        $this->assertEquals(1, count($this->usrMsgRepo->findBy(array('isRead' => true))));
    }

    public function testMarkAsRemoved()
    {
        $um = $this->usrMsgRepo->findAll();
        $this->writer->markAsRemoved($um[0]);
        $this->assertEquals(1, count($this->usrMsgRepo->findBy(array('isRemoved' => true))));
    }

    public function testMarkAsUnremoved()
    {
        $um = $this->usrMsgRepo->findAll();
        $this->writer->markAsRemoved($um[0]);
        $this->writer->markAsUnremoved($um[0]);
        $this->assertEquals(0, count($this->usrMsgRepo->findBy(array('isRemoved' => true))));
    }

    public function testRemove()
    {
        $um = $this->usrMsgRepo->findAll();
        $this->assertEquals(2, count($um));
        $this->writer->remove($um[0]);
        $um = $this->usrMsgRepo->findAll();
        $this->assertEquals(1, count($um));
    }
}
