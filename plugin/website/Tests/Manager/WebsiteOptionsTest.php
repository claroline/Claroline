<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 3/22/16
 */

namespace Icap\WebsiteBundle\Tests;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Icap\WebsiteBundle\Entity\Website;
use Icap\WebsiteBundle\Manager\WebsiteOptionsManager;
use Icap\WebsiteBundle\Testing\Persister;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WebsiteOptionsTest extends TransactionalTestCase
{
    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;
    /** @var Website */
    private $website;
    /** @var WebsiteOptionsManager */
    private $websiteOptionsManager;
    private $file;
    /** @var UploadedFile */
    private $image;
    private $websiteOptionParams;

    private $webDir;

    protected function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->webDir = $container->getParameter('claroline.param.web_directory');
        $this->websiteOptionsManager = $container->get('icap.website.options.manager');
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->persist = new Persister($this->om);
        $user = $this->persist->user('john');
        $this->website = $this->persist->website('Test Website', $user);
        $this->websiteOptionParams = [
            'bannerText' => 'banner',
            'footerText' => 'footer',
        ];
        $this->file = tempnam(sys_get_temp_dir(), 'upl');
        imagepng(imagecreatetruecolor(10, 10), $this->file);
        $this->image = new UploadedFile(
            $this->file,
            'new_image.png',
            null,
            null,
            null,
            true
        );
    }

    public function testUpdate()
    {
        $repo = $this->om->getRepository('IcapWebsiteBundle:WebsiteOptions');
        $this->websiteOptionsManager->processForm($this->website->getOptions(), $this->websiteOptionParams, 'PUT');
        $this->assertEquals('banner', $repo->findOneBy(['website' => $this->website])->getBannerText(), 'Test Website options update');
    }

    public function testUpload()
    {
        $repo = $this->om->getRepository('IcapWebsiteBundle:WebsiteOptions');
        $this->websiteOptionsManager->handleUploadImageFile($this->website->getOptions(), $this->image, 'bannerBgImage');
        $this->assertNotNull($repo->findOneBy(['website' => $this->website])->getBannerBgImage(), 'Test Website options update');
    }

    protected function tearDown()
    {
        $this->persist->deleteWebsiteTestsFolder($this->website, $this->webDir);
        parent::tearDown();
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }
}
