<?php

namespace Innova\VideoRecorderBundle\Manager;

use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Claroline\CoreBundle\Entity\User;
use Innova\VideoRecorderBundle\Testing\Persister;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class VideoRecorderManagerTest extends TransactionalTestCase
{
    /** @var ResourceManager */
    private $rm;

    /** @var ObjectManager */
    private $om;

    /** @var ContainerInterface */
    private $container;

    /** @var VideoRecorderManager */
    private $manager;

    /** @var string */
    private $fileDir;

    /** @var string */
    private $uploadDir;

    /** @var UploadedFile */
    private $uploadedFile;

    /** @var User */
    private $bob;

    /** @var Persister */
    private $persist;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->client->getContainer();
        $this->rm = $this->container->get('claroline.manager.resource_manager');
        $this->uploadedFile = $this->mock('Symfony\Component\HttpFoundation\File\UploadedFile');
        $this->fileDir = $this->container->getParameter('claroline.param.files_directory');
        $this->uploadDir = $this->container->getParameter('claroline.param.uploads_directory');
        $this->om = $this->container->get('claroline.persistence.object_manager');

        $this->manager = new VideoRecorderManager($this->container, $this->rm, $this->fileDir, $this->uploadDir);

        $this->persist = new Persister($this->om);
        $this->bob = $this->persist->user('bob', true);
        $this->om->flush();
    }

    public function testParams()
    {
        $data = ['fileName' => 'file'];
        $result = $this->manager->validateParams($data, $this->uploadedFile);
        $this->assertTrue($result);
    }

    public function testWrongParams()
    {
        $data = [];
        $result = $this->manager->validateParams($data, $this->uploadedFile);
        $this->assertFalse($result);
    }

    public function testGetBaseFileHashWithWorkspace()
    {
        $uniqueBaseName = 'video_recorder_test_unique_name';
        $hashName = $this->manager->getBaseFileHashName($uniqueBaseName, $this->bob->getPersonalWorkspace());
        $wId = (string) $this->bob->getPersonalWorkspace()->getId();
        $this->assertEquals('WORKSPACE_'.$wId.'/'.$uniqueBaseName, $hashName);
    }

    public function testGetBaseFileHashWithoutWorkspace()
    {
        $firewall = 'secured_area';
        $token = new UsernamePasswordToken('bill', null, $firewall, array('ROLE_USER'));
        $this->container->get('security.token_storage')->setToken($token);

        $uniqueBaseName = 'video_recorder_test_unique_name';
        $hashName = $this->manager->getBaseFileHashName($uniqueBaseName);
        $this->assertEquals('bill/'.$uniqueBaseName, $hashName);
    }

    /**
     * Failure test
     * Can not go with success test since I dont now how to simulate moked file avconv conversion....
     **/
    public function testUploadFileAndCreateResourceParamsError()
    {
        $data = [];
        $result = $this->manager->uploadFileAndCreateResource($data, $this->uploadedFile, $this->bob->getPersonalWorkspace());
        $this->assertEquals(null, $result['file']);
        $this->assertEquals(1, count($result['errors'][0]));
        $this->assertEquals('one or more request parameters are missing.', $result['errors'][0]);
    }

    private function mock($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
