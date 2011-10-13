<?php

namespace Claroline\PluginBundle\Service\ApplicationManager;

use Claroline\PluginBundle\Tests\PluginBundleTestCase;
use Claroline\PluginBundle\Service\ApplicationManager\Exception\ApplicationException;

class ManagerTest extends PluginBundleTestCase
{
    /** Claroline\PluginBundle\Service\ApplicationManager\Manager */
    private $appManager;    
    /** Claroline\PluginBundle\Repository\ApplicationRepository */
    private $appRepo;
    
    public function setUp()
    {
        parent::setUp();        
        $container = $this->client->getContainer();
        $this->appManager = $container->get('claroline.plugin.application_manager');
        $this->appRepo = $container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\PluginBundle\Entity\Application');
        
        $this->client->beginTransaction();
    }
    
    public function tearDown()
    {
        $this->client->rollback();
    }
    
    public function testMarkAsPlatformIndexThrowsExceptionOnNonExistentApplicationArgument()
    {
        try
        {
            $this->appManager->markAsPlatformIndex('NonExistentApplicationFQCN');
            $this->fail("No exception thrown.");
        }
        catch (ApplicationException $ex)
        {
            $this->assertEquals(ApplicationException::NON_EXISTENT_APPLICATION, $ex->getCode());
        }
    }
    
    public function testMarkAsPlatformIndexThrowsExceptionIfApplicationIsNotEligible()
    {
        try
        {
            $appFqcn = 'ValidApplication\Minimal\ValidApplicationMinimal';
            $this->manager->install($appFqcn);
            $this->appManager->markAsPlatformIndex($appFqcn);
            $this->fail("No exception thrown.");
        }
        catch (ApplicationException $ex)
        {
            $this->assertEquals(ApplicationException::NOT_ELIGIBLE_APPLICATION, $ex->getCode());
        }
    }
    
    public function testMarkAsPlatformIndexElectsApplicationEvenIfNoPriorIndexApplicationIsSet()
    {
        $nonEligibleAppFqcn = 'ValidApplication\Minimal\ValidApplicationMinimal';
        $eligibleAppFqcn = 'ValidApplication\EligibleForIndex1\ValidApplicationEligibleForIndex1';
        $this->manager->install($nonEligibleAppFqcn);
        $this->manager->install($eligibleAppFqcn);
        
        $this->appManager->markAsPlatformIndex($eligibleAppFqcn);
        
        $indexApp = $this->appRepo->getIndexApplication();
        $this->assertEquals($eligibleAppFqcn, $indexApp->getBundleFQCN());
    }
    
    public function testMarkAsPlatformIndexUnsetsPriorIndexApplication()
    {
        $firstEligibleAppFqcn = 'ValidApplication\EligibleForIndex1\ValidApplicationEligibleForIndex1';
        $secondEligibleAppFqcn = 'ValidApplication\EligibleForIndex2\ValidApplicationEligibleForIndex2';
        $this->manager->install($firstEligibleAppFqcn);
        $this->manager->install($secondEligibleAppFqcn);
        
        $this->appManager->markAsPlatformIndex($firstEligibleAppFqcn);
        $this->appManager->markAsPlatformIndex($secondEligibleAppFqcn);
        
        $indexApp = $this->appRepo->getIndexApplication();
        $this->assertEquals($secondEligibleAppFqcn, $indexApp->getBundleFQCN());
        $oldIndexApp = $this->appRepo->findOneByBundleFQCN($firstEligibleAppFqcn);
        $this->assertFalse($oldIndexApp->isPlatformIndex());
    }
    
    public function testMarkAsPlatformIndexCanSafelyBeCalledSeveralTimesOnSameApplication()
    {
        $eligibleAppFqcn = 'ValidApplication\EligibleForIndex1\ValidApplicationEligibleForIndex1';
        $this->manager->install($eligibleAppFqcn);
        
        $this->appManager->markAsPlatformIndex($eligibleAppFqcn);
        $this->appManager->markAsPlatformIndex($eligibleAppFqcn);
    }
}