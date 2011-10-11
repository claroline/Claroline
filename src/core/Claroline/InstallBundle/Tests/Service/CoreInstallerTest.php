<?php
namespace Claroline\InstallBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Claroline\InstallBundle\Service\CoreInstaller;
use Symfony\Component\HttpKernel\Kernel;
use Claroline\InstallBundle\Service\BundleMigrator;


class CoreInstallerTest extends TestCase
{
    private $coreInstaller;
    
    protected function setUp() {
        parent::setUp();
    }
    
    public function testTruth()
    {
        $this->assertTrue(true);
    }
    
}