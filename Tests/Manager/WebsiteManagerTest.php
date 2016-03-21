<?php
/**
 * Created by PhpStorm.
 * User: ptsavdar
 * Date: 17/03/16
 * Time: 11:36
 */

namespace Icap\WebsiteBundle\Tests;


use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Persistence\ObjectManager;
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
    /** @var  WebsitePageManager */
    private $pageManager;
    /** @var  WebsiteManager */
    private $websiteManager;

    private $websitePageParams;

    protected function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->pageManager = $container->get('icap.website.page.manager');
        $this->websiteManager = $container->get('icap.website.manager');
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->persist = new Persister($this->om);
        $this->websitePageParams = array(
            'title'             => 'Test page',
            'type'              => WebsitePageTypeEnum::BLANK_PAGE,
            'description'       => 'Test description',
            'visible'           => true,
            'isSection'         => false,
            'richText'          => '<div>this is a test page</div>'
        );
    }

    public function testWebsiteCopy()
    {
        $pageRepo = $this->om->getRepository('IcapWebsiteBundle:WebsitePage');
        $websiteRepo = $this->om->getRepository('IcapWebsiteBundle:Website');
        $user = $this->persist->user('john');
        $website = $this->persist->website('Test Website', $user);
        for ($i = 0; $i < 3; $i++) {
            $page = $this->pageManager->createEmptyPage($website, $website->getRoot());
            $this->websitePageParams['title'] = 'Page' . ($i + 1);
            $this->pageManager->processForm($website, $page, $this->websitePageParams, "POST");
        }
        $this->websiteManager->copyWebsite($website);
        $this->assertEquals(2, count($websiteRepo->findAll()), "Test if copy of Website was created");
        $this->assertEquals(8, count($pageRepo->findAll()), "Test if all pages where correctly created in Website copy");
    }
}