<?php

namespace Claroline\CoreBundle\Library\Workspace;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class CreatorTest extends FunctionalTestCase
{
    /** @var Creator */
    private $creator;

    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('user' => 'user'));
        $this->creator = $this->client->getContainer()->get('claroline.workspace.creator');
    }

    /**
     * @dataProvider invalidConfigProvider
     */
    public function testWorkspaceConfigurationIsCheckedBeforeCreation()
    {
        $this->markTestSkipped('How to set params to a data provider ?');
        $this->setExpectedException('RuntimeException');
    }

    public function testWorkspaceCreatedWithMinimalConfigurationHasDefaultParameters()
    {
        $personalWsTemplateFile = $this->client
            ->getContainer()->getParameter('claroline.param.templates_directory')."default.zip";
        $config = new Configuration($personalWsTemplateFile);
        $config->setWorkspaceName('Workspace Foo');
        $config->setWorkspaceCode('WFOO');
        $user = $this->getUser('user');

        $workspace = $this->creator->createWorkspace($config, $user);

        $this->assertEquals(Configuration::TYPE_SIMPLE, get_class($workspace));
        $this->assertEquals('Workspace Foo', $workspace->getName());
        $roleRepo = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Role');
        $this->assertEquals('visitor', $roleRepo->findVisitorRole($workspace)->getTranslationKey());
        $this->assertEquals('collaborator', $roleRepo->findCollaboratorRole($workspace)->getTranslationKey());
        $this->assertEquals('manager', $roleRepo->findManagerRole($workspace)->getTranslationKey());
    }

    public function invalidConfigProvider()
    {
        /*
        $firstConfig = new Configuration(); // workspace name is required
        $secondConfig = new Configuration();
        $secondConfig->setWorkspaceName('Workspace X');
        $secondConfig->setWorkspaceType('Some\Type'); // invalid workspace type

        return array(
            array($firstConfig),
            array($secondConfig)
        );
        */
    }
}