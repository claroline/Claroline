<?php

namespace Claroline\PluginBundle\Manager\ApplicationManager;

use Claroline\CommonBundle\Library\Testing\TransactionalTestCase;
use Claroline\PluginBundle\Installer\Loader;
use Claroline\PluginBundle\Exception\ApplicationException;

class ApplicationManagerTest extends TransactionalTestCase
{
    /** Claroline\PluginBundle\Manager\ApplicationManager */
    private $appManager;
    
    /** Claroline\PluginBundle\Repository\ApplicationRepository */
    private $appRepo;
    
    /** Claroline\PluginBundle\Installer\Loader */
    private $loader;
    
    /** Claroline\PluginBundle\Installer\Recorder\Writer\DatabaseWriter */
    private $dbRecorder;
    
    public function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->appManager = $container->get('claroline.plugin.application_manager');
        $this->appRepo = $container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\PluginBundle\Entity\Application');
        $this->loader = $container->get('claroline.plugin.loader');
        $this->dbRecorder = $container->get('claroline.plugin.recorder_database_writer');
        $this->overrideDefaultPluginDirectories($this->loader);
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
            $appFqcn = 'Valid\Basic\ValidBasic';
            $this->installApplication($appFqcn);
            $this->appManager->markAsPlatformIndex($appFqcn);
            $this->fail("No exception thrown.");
        }
        catch (ApplicationException $ex)
        {
            $this->assertEquals(ApplicationException::NOT_ELIGIBLE_FOR_PLATFORM_INDEX, $ex->getCode());
        }
    }
    
    public function testMarkAsPlatformIndexElectsApplicationEvenIfNoPriorIndexApplicationIsSet()
    {
        $nonEligibleAppFqcn = 'Valid\Basic\ValidBasic';
        $eligibleAppFqcn = 'Valid\EligibleForIndex1\ValidEligibleForIndex1';
        $this->installApplication($nonEligibleAppFqcn);
        $this->installApplication($eligibleAppFqcn);
        
        $this->appManager->markAsPlatformIndex($eligibleAppFqcn);
        
        $indexApp = $this->appRepo->getIndexApplication();
        $this->assertEquals($eligibleAppFqcn, $indexApp->getBundleFQCN());
    }
    
    public function testMarkAsPlatformIndexUnsetsPriorIndexApplication()
    {
        $firstEligibleAppFqcn = 'Valid\EligibleForIndex1\ValidEligibleForIndex1';
        $secondEligibleAppFqcn = 'Valid\EligibleForIndex2\ValidEligibleForIndex2';
        $this->installApplication($firstEligibleAppFqcn);
        $this->installApplication($secondEligibleAppFqcn);
        
        $this->appManager->markAsPlatformIndex($firstEligibleAppFqcn);
        $this->appManager->markAsPlatformIndex($secondEligibleAppFqcn);
        
        $indexApp = $this->appRepo->getIndexApplication();
        $this->assertEquals($secondEligibleAppFqcn, $indexApp->getBundleFQCN());
        $oldIndexApp = $this->appRepo->findOneByBundleFQCN($firstEligibleAppFqcn);
        $this->assertFalse($oldIndexApp->isPlatformIndex());
    }
    
    public function testMarkAsPlatformIndexCanSafelyBeCalledSeveralTimesOnSameApplication()
    {
        $eligibleAppFqcn = 'Valid\EligibleForIndex1\ValidEligibleForIndex1';
        $this->installApplication($eligibleAppFqcn);
        
        $this->appManager->markAsPlatformIndex($eligibleAppFqcn);
        $this->appManager->markAsPlatformIndex($eligibleAppFqcn);
    }
    
    public function testMarkAsConnectionTargetThrowsExceptionOnNonExistentApplicationArgument()
    {
        try
        {
            $this->appManager->markAsConnectionTarget('NonExistentApplicationFQCN');
            $this->fail("No exception thrown.");
        }
        catch (ApplicationException $ex)
        {
            $this->assertEquals(ApplicationException::NON_EXISTENT_APPLICATION, $ex->getCode());
        }
    }
    
    public function testMarkAsConnectionTargetThrowsExceptionIfApplicationIsNotEligible()
    {
        try
        {
            $appFqcn = 'Valid\Basic\ValidBasic';
            $this->installApplication($appFqcn);
            $this->appManager->markAsConnectionTarget($appFqcn);
            $this->fail("No exception thrown.");
        }
        catch (ApplicationException $ex)
        {
            $this->assertEquals(ApplicationException::NOT_ELIGIBLE_FOR_CONNECTION_TARGET, $ex->getCode());
        }
    }
    
    public function testMarkAsConnectionTargetElectsApplicationEvenIfNoPriorTargetIsSet()
    {
        $nonEligibleAppFqcn =  'Valid\Basic\ValidBasic';
        $eligibleAppFqcn = 'Valid\EligibleForConnectionTarget1\ValidEligibleForConnectionTarget1';
        $this->installApplication($nonEligibleAppFqcn);
        $this->installApplication($eligibleAppFqcn);
        
        $this->appManager->markAsConnectionTarget($eligibleAppFqcn);
        
        $targetApp = $this->appRepo->getConnectionTargetApplication();
        $this->assertEquals($eligibleAppFqcn, $targetApp->getBundleFQCN());
    }
    
    public function testMarkAsConnectionTargetUnsetsPriorTargetApplication()
    {
        $firstEligibleAppFqcn = 'Valid\EligibleForConnectionTarget1\ValidEligibleForConnectionTarget1';
        $secondEligibleAppFqcn = 'Valid\EligibleForConnectionTarget2\ValidEligibleForConnectionTarget2';
        $this->installApplication($firstEligibleAppFqcn);
        $this->installApplication($secondEligibleAppFqcn);
        
        $this->appManager->markAsConnectionTarget($firstEligibleAppFqcn);
        $this->appManager->markAsConnectionTarget($secondEligibleAppFqcn);
        
        $targetApp = $this->appRepo->getConnectionTargetApplication();
        $this->assertEquals($secondEligibleAppFqcn, $targetApp->getBundleFQCN());
        $oldTargetApp = $this->appRepo->findOneByBundleFQCN($firstEligibleAppFqcn);
        $this->assertFalse($oldTargetApp->isConnectionTarget());
    }
        
    public function testMarkAsConnectionTargetCanSafelyBeCalledSeveralTimesOnSameApplication()
    {
        $eligibleAppFqcn = 'Valid\EligibleForConnectionTarget1\ValidEligibleForConnectionTarget1';
        $this->installApplication($eligibleAppFqcn);
        
        $this->appManager->markAsConnectionTarget($eligibleAppFqcn);
        $this->appManager->markAsConnectionTarget($eligibleAppFqcn);
    }
    
    private function installApplication($appFqcn)
    {
        $app = $this->loader->load($appFqcn);
        $this->dbRecorder->insert($app);
    }
    
    private function overrideDefaultPluginDirectories(Loader $loader)
    {
        $ds = DIRECTORY_SEPARATOR;
        $stubDir = __DIR__ . "{$ds}..{$ds}stub{$ds}plugin{$ds}";
        $loader->setPluginDirectories(
            array(
                'extension' => "{$stubDir}extension",
                'application' => "{$stubDir}application",
                'tool' => "{$stubDir}tool"
            )
        );
    }
}