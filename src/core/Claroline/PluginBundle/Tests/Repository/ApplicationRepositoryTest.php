<?php

namespace Claroline\PluginBundle\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\PluginBundle\Entity\Application;
use Claroline\PluginBundle\Service\ApplicationManager\Exception\ApplicationException;
use Claroline\Lib\Testing\TransactionalTestCase;

class ApplicationRepositoryTest extends TransactionalTestCase
{

    /** Doctrine\ORM\Entity */
    private $em;
    /** Claroline\PluginBundle\Repository\ApplicationRepository */
    private $appRepo;
    
    public function setUp()
    {
        parent :: setUp();
        $container = $this->client->getContainer();
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->appRepo = $this->em->getRepository('Claroline\PluginBundle\Entity\Application');
    }
    
    public function testGetIndexApplicationReturnsExpectedApplication()
    {
        $apps = $this->getTwoApplicationEntities();
        $apps[0]->setIsPlatformIndex(true);
        
        $this->em->persist($apps[0]);    
        $this->em->flush();
        
        $indexApp = $this->appRepo->getIndexApplication();
        
        $this->assertEquals($apps[0]->getId(), $indexApp->getId());
        $this->assertEquals($apps[0]->getBundleName(), $indexApp->getBundleName());
    }
    
    public function testGetIndexApplicationReturnsFalseIfNoIndexApplicationIsSet()
    {
        $this->assertFalse($this->appRepo->getIndexApplication());
    }
    
    public function testGetIndexApplicationThrowsAnExceptionIfMultiplesIndexApplicationsAreSet()
    {        
        $apps = $this->getTwoApplicationEntities();
        $apps[0]->setIsPlatformIndex(true);
        $apps[1]->setIsPlatformIndex(true);
        
        $this->em->persist($apps[0]);
        $this->em->persist($apps[1]);      
        $this->em->flush();
        
        try
        {
            $this->appRepo->getIndexApplication();
            $this->fail('No exception thrown');
        }
        catch (ApplicationException $ex)
        {
            // Note: using the application manager to mark an application
            // as platform index prevents this situation. 
            $this->assertEquals(ApplicationException::MULTIPLES_INDEX_APPLICATIONS, $ex->getCode());
        }
    }
      
    public function testGetConnectionTargetApplicationReturnsExpectedApplication()
    {
        $apps = $this->getTwoApplicationEntities();
        $apps[0]->setIsConnectionTarget(true);
        
        $this->em->persist($apps[0]);    
        $this->em->flush();
        
        $indexApp = $this->appRepo->getConnectionTargetApplication();
        
        $this->assertEquals($apps[0]->getId(), $indexApp->getId());
        $this->assertEquals($apps[0]->getBundleName(), $indexApp->getBundleName());
    }
    
    public function testGetConnectionTargetApplicationReturnsFalseIfNoTargetApplicationIsSet()
    {
        $this->assertFalse($this->appRepo->getConnectionTargetApplication());
    }
    
    public function testGetConnectionTargetApplicationThrowsAnExceptionIfMultiplesTargetsAreSet()
    {        
        $apps = $this->getTwoApplicationEntities();
        $apps[0]->setIsConnectionTarget(true);
        $apps[1]->setIsConnectionTarget(true);
        
        $this->em->persist($apps[0]);
        $this->em->persist($apps[1]);      
        $this->em->flush();
        
        try
        {
            $this->appRepo->getConnectionTargetApplication();
            $this->fail('No exception thrown');
        }
        catch (ApplicationException $ex)
        {
            // Note: using the application manager to mark an application
            // as connection target prevents this situation. 
            $this->assertEquals(ApplicationException::MULTIPLES_INDEX_APPLICATIONS, $ex->getCode());
        }
    }
    
    private function getTwoApplicationEntities()
    {
        $firstApp = new Application();
        $firstApp->setType('App');
        $firstApp->setBundleFQCN('VendorX\FirstAppBundle\VendorXFirstAppBundle');
        $firstApp->setVendorName('VendorX');
        $firstApp->setBundleName('FirstAppBundle');
        $firstApp->setNameTranslationKey('name_key_1');
        $firstApp->setDescriptionTranslationKey('desc_key_1');
        $firstApp->setIndexRoute('index_route_1');
        
        $secondApp = new Application();
        $secondApp->setType('App');
        $secondApp->setBundleFQCN('VendorX\SecondAppBundle\VendorXSecondAppBundle');
        $secondApp->setVendorName('VendorX');
        $secondApp->setBundleName('SecondAppBundle');
        $secondApp->setNameTranslationKey('name_key_2');
        $secondApp->setDescriptionTranslationKey('desc_key_2');
        $secondApp->setIndexRoute('index_route_2');
        
        return array($firstApp, $secondApp);
    }
}