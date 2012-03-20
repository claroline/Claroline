<?php
namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;

class ResourceControllerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        
        $this->loadUserFixture();
        $this->loadFixture(new LoadResourceTypeData());
        $this->client->followRedirects();
    }

    //this test works with the file controller
    public function testUserCanCreateFileResource()
    {
        //will be needed for the file controller
        $ds = DIRECTORY_SEPARATOR; 
        $filePath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}originalFile.txt";
        //test
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', '/desktop');
        $link = $crawler->filter('#resource_manager_link')->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(1, count($crawler->filterXpath("//select/option")));
        $form = $crawler->filter('input[type=submit]')->form(); 
        $form['choose_resource_form[type]'] = $this->getFixtureReference('resource_type/file')->getId();
        $crawler = $this->client->submit($form);
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('File_Form[file]' => $filePath));
        $this->assertTrue($crawler->filter('#resource_manager_link')->count() > 0);        
    }    
}    
    