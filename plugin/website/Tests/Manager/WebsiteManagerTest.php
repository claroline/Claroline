<?php
/**
 * Created by PhpStorm.
 * User: ptsavdar
 * Date: 17/03/16
 * Time: 11:36.
 */

namespace Icap\WebsiteBundle\Tests;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Icap\WebsiteBundle\Entity\Website;
use Icap\WebsiteBundle\Entity\WebsitePageTypeEnum;
use Icap\WebsiteBundle\Manager\WebsiteManager;
use Icap\WebsiteBundle\Manager\WebsitePageManager;
use Icap\WebsiteBundle\Testing\Persister;

class WebsiteManagerTest extends TransactionalTestCase
{
    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;
    /** @var WebsitePageManager */
    private $pageManager;
    /** @var WebsiteManager */
    private $websiteManager;
    /** @var Website */
    private $website;
    /** @var User */
    private $user;

    private $websitePageParams;

    private $webDir;

    protected function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->webDir = $container->getParameter('claroline.param.web_directory');
        $this->pageManager = $container->get('icap.website.page.manager');
        $this->websiteManager = $container->get('icap.website.manager');
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->persist = new Persister($this->om);
        $this->user = $this->persist->user('john');
        $this->website = $this->persist->website('Test Website', $this->user);
        $this->websitePageParams = [
            'title' => 'Test page',
            'type' => WebsitePageTypeEnum::BLANK_PAGE,
            'description' => 'Test description',
            'visible' => true,
            'isSection' => false,
            'richText' => '<div>this is a test page</div>',
        ];
    }

    public function testCopy()
    {
        $pageRepo = $this->om->getRepository('IcapWebsiteBundle:WebsitePage');
        $websiteRepo = $this->om->getRepository('IcapWebsiteBundle:Website');
        for ($i = 0; $i < 3; ++$i) {
            $page = $this->pageManager->createEmptyPage($this->website, $this->website->getRoot());
            $this->websitePageParams['title'] = 'Page'.($i + 1);
            $this->pageManager->processForm($this->website, $page, $this->websitePageParams, 'POST');
        }
        $this->websiteManager->copyWebsite($this->website);
        $this->assertEquals(2, count($websiteRepo->findAll()), 'Test if copy of Website was created');
        $this->assertEquals(8, count($pageRepo->findAll()), 'Test if all pages were correctly created in Website copy');
    }

    public function testExportAndImport()
    {
        $pageRepo = $this->om->getRepository('IcapWebsiteBundle:WebsitePage');
        $websiteRepo = $this->om->getRepository('IcapWebsiteBundle:Website');
        for ($i = 0; $i < 3; ++$i) {
            $page = $this->pageManager->createEmptyPage($this->website, $this->website->getRoot());
            $this->websitePageParams['title'] = 'Page'.($i + 1);
            $this->pageManager->processForm($this->website, $page, $this->websitePageParams, 'POST');
        }
        $files = null;
        $data = $this->websiteManager->exportWebsite($this->user->getPersonalWorkspace(), $files, $this->website);
        $this->assertEquals(4, count($data['pages']), 'Test Website export');
        $this->websiteManager->importWebsite(['data' => $data], null, [], true);
        $this->assertEquals(2, count($websiteRepo->findAll()), 'Test if Website was imported correctly');
        $this->assertEquals(8, count($pageRepo->findAll()), 'Test if all Website pages were imported correctly');
    }

    protected function tearDown()
    {
        $this->persist->deleteWebsiteTestsFolder($this->website, $this->webDir);
        parent::tearDown();
    }
}
