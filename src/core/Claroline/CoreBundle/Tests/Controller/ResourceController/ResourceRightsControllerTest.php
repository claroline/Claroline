<?php

namespace Claroline\CoreBundle\Controller\ResourceController;

Use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadFileData;

class ResourceRightsControllerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('user', 'admin'));
        $this->client->followRedirects();
        $this->pwr = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->getRootForWorkspace($this->getFixtureReference('user/user')->getPersonalWorkspace());
    }

    public function tearDown()
    {
        $this->cleanDirectory($this->client->getContainer()->getParameter('claroline.files.directory'));
        $this->cleanDirectory($this->client->getContainer()->getParameter('claroline.thumbnails.directory'));
        parent::tearDown();
    }

    public function testDisplayRightsForm()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $file = $this->uploadFile($this->pwr, 'file');
        $crawler = $this->client->request('GET', "/resource/{$file->getId()}/rights/form");
        $this->assertEquals(4, count($crawler->filter('.row-rights')));
    }

    public function testSubmitRightsForm()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $file = $this->uploadFile($this->pwr, 'file');
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $resourceRights = $em->getRepository('ClarolineCoreBundle:Rights\ResourceRights')
            ->findBy(array('resource' => $file));
        $this->assertEquals(4, count($resourceRights));

        //changes keep the 1st $resourceRight and change the others

        $this->client->request(
            'POST',
            "/resource/{$file->getId()}/rights/edit",
            array(
                 "canView-{$resourceRights[0]->getId()}" => true,
                 "canView-{$resourceRights[1]->getId()}" => true,
                 "canDelete-{$resourceRights[1]->getId()}" => true,
             )
        );

        $resourceRights = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Rights\ResourceRights')
            ->findBy(array('resource' => $file));
        $this->assertEquals(4, count($resourceRights));
        $this->assertTrue($resourceRights[0]->isEquals(array(
            'canView' => true,
            'canCopy' => false,
            'canDelete' => false,
            'canEdit' => false,
            'canOpen' => false,
            'canCreate' => false,
            'canExport' => false
        )));

        $this->assertTrue($resourceRights[1]->isEquals(array(
            'canView' => true,
            'canCopy' => false,
            'canDelete' => true,
            'canEdit' => false,
            'canOpen' => false,
            'canCreate' => false,
            'canExport' => false
        )));

        $this->assertTrue($resourceRights[2]->isEquals(array(
            'canView' => false,
            'canCopy' => false,
            'canDelete' => false,
            'canEdit' => false,
            'canOpen' => false,
            'canCreate' => false,
            'canExport' => false
        )));
    }

    public function testDisplayCreationRightForm()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $collaboratorRole = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Role')
            ->getCollaboratorRole($this->getFixtureReference('user/user')
                ->getPersonalWorkspace());

        $dir = $this->createDirectory($this->pwr, 'dir');
        $crawler = $this->client->request(
            'GET',
            "/resource/{$dir->getId()}/role/{$collaboratorRole->getId()}/right/creation/form"
        );
        $this->assertEquals(1, count($crawler->filter('#form-resource-creation-rights')));
    }

    public function testSubmitRightsCreationForm()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $collaboratorRole = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Role')
            ->getCollaboratorRole($this->getFixtureReference('user/user')
                ->getPersonalWorkspace());
        $dir = $this->createDirectory($this->pwr, 'dir');
        $resourceTypes = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findBy(array('isVisible' => true));

        //Creating new ResourceRight from the default one
        $this->client->request(
            'POST',
            "/resource/{$dir->getId()}/role/{$collaboratorRole->getId()}/right/creation/edit",
            array(
                "create-{$resourceTypes[0]->getId()}" => true,
                "create-{$resourceTypes[1]->getId()}" => true,
            )
        );

        //checks if the creation right is set to true now
        $config = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Rights\ResourceRights')
            ->findOneBy(array('resource' => $dir->getId(), 'role' => $collaboratorRole));

        $permCreate = $config->getResourceTypes();
        $this->assertEquals(2, count($permCreate));

        //updating the new right
        $this->client->request(
            'POST',
            "/resource/{$dir->getId()}/role/{$collaboratorRole->getId()}/right/creation/edit",
            array(
                "create-{$resourceTypes[1]->getId()}" => true,
                "create-{$resourceTypes[2]->getId()}" => true,
                "create-{$resourceTypes[3]->getId()}" => true,
            )
        );

        $config = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Rights\ResourceRights')
            ->findOneBy(array('resource' => $dir->getId(), 'role' => $collaboratorRole));

        $permCreate = $config->getResourceTypes();
        $this->assertEquals(3, count($permCreate));
    }

    private function uploadFile($parent, $name)
    {
        $user = $this->client->getContainer()->get('security.context')->getToken()->getUser();
        $fileData = new LoadFileData($name, $parent, $user, tempnam(sys_get_temp_dir(), 'FormTest'));
        $this->loadFixture($fileData);

        return $fileData->getLastFileCreated();
    }

    private function createDirectory($parent, $name)
    {
        $manager = $this->client->getContainer()->get('claroline.resource.manager');
        $user = $this->client->getContainer()->get('security.context')->getToken()->getUser();
        $directory = new Directory();
        $directory->setName($name);
        $dir = $manager->create($directory, $parent->getId(), 'directory', $user);

        return $dir;
    }

    private function cleanDirectory($dir)
    {
        $iterator = new \DirectoryIterator($dir);

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() !== 'placeholder'
                && $file->getFilename() !== 'originalFile.txt'
                && $file->getFilename() !== 'originalZip.zip'
            ) {
                chmod($file->getPathname(), 0777);
                unlink($file->getPathname());
            }
        }
    }
}
