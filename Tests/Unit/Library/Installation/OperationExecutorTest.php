<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation;

class OperationExecutorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OperationExecutor
     */
    private $executor;
    private $kernel;
    private $detector;

    protected function setUp()
    {
        $this->kernel = $this->mock('Symfony\Component\HttpKernel\KernelInterface');
        $installManager = $this->mock('Claroline\InstallationBundle\Manager\InstallationManager');
        $pluginInstaller = $this->mock('Claroline\CoreBundle\Library\Installation\Plugin\Installer');
        $this->detector = $this->mock('Claroline\BundleRecorder\Detector\Detector');
        $this->executor = new OperationExecutor($this->kernel, $installManager, $pluginInstaller);
        $this->executor->setBundleDetector($this->detector);

        // always build a fake fqcn based on the given path
        $this->detector->expects($this->any())
            ->method('detectBundle')
            ->willReturnCallback(function ($path) {
                $parts = explode('/', $path);
                $name = array_pop($parts);

                return ucfirst($name);
            });
    }

    /**
     * @dataProvider missingRepoProvider
     * @expectedException \Claroline\CoreBundle\Library\Installation\ExecutorException
     * @expectedExceptionCode 11
     */
    public function testBuildOperationListThrowsIfRepoIsMissing($previous, $installed)
    {
        $this->executor->setRepositoryFiles($previous, $installed);
        $this->executor->buildOperationList();
    }

    /**
     * @dataProvider notJsonRepoProvider
     * @expectedException \Claroline\CoreBundle\Library\Installation\ExecutorException
     * @expectedExceptionCode 12
     */
    public function testBuildOperationListExecutorThrowsIfRepoIsNotJson($previous, $installed)
    {
        $this->executor->setRepositoryFiles($previous, $installed);
        $this->executor->buildOperationList();
    }

    /**
     * @dataProvider notArrayRepoProvider
     * @expectedException \Claroline\CoreBundle\Library\Installation\ExecutorException
     * @expectedExceptionCode 13
     */
    public function testOperationListThrowsIfRepoIsNotArray($previous, $installed)
    {
        $this->executor->setRepositoryFiles($previous, $installed);
        $this->executor->buildOperationList();
    }

    public function testBuildOperationListInstallOnly()
    {
        $this->executor->setRepositoryFiles($this->repo('empty'), $this->repo('repo-1'));
        $this->kernel->expects($this->once())->method('getBundles')->willReturn([
            $this->mockBundle('Foo'),
            $this->mockBundle('Bar')
        ]);

        $operations = $this->executor->buildOperationList();
        $this->assertEquals(2, count($operations));
        $this->assertEquals($operations[0]->getPackageName(), 'foo');
        $this->assertEquals($operations[0]->getBundleFqcn(), 'Foo');
        $this->assertEquals($operations[0]->getPackageType(), 'claroline-core');
        $this->assertEquals($operations[0]->getType(), Operation::INSTALL);
        $this->assertEquals($operations[1]->getPackageName(), 'bar');
        $this->assertEquals($operations[1]->getBundleFqcn(), 'Bar');
        $this->assertEquals($operations[1]->getPackageType(), 'claroline-plugin');
        $this->assertEquals($operations[1]->getType(), Operation::INSTALL);
    }

    public function testBuildOperationListUpdateOnly()
    {
        $this->executor->setRepositoryFiles($this->repo('repo-1'), $this->repo('repo-2'));
        $this->kernel->expects($this->once())->method('getBundles')->willReturn([
            $this->mockBundle('Foo')
        ]);

        $operations = $this->executor->buildOperationList();
        $this->assertEquals(1, count($operations));
        $this->assertEquals($operations[0]->getPackageName(), 'foo');
        $this->assertEquals($operations[0]->getBundleFqcn(), 'Foo');
        $this->assertEquals($operations[0]->getPackageType(), 'claroline-core');
        $this->assertEquals($operations[0]->getType(), Operation::UPDATE);
        $this->assertEquals($operations[0]->getFromVersion(), '1.0.0.0');
        $this->assertEquals($operations[0]->getToVersion(), '2.0.0.0');
    }

    public function testBuildOperationsMixedAndSorted()
    {
        $this->executor->setRepositoryFiles($this->repo('repo-1'), $this->repo('repo-3'));
        $this->kernel->expects($this->once())->method('getBundles')->willReturn([
            $this->mockBundle('Foo'),
            $this->mockBundle('Quz'),
            $this->mockBundle('Bar')
        ]);
        $operations = $this->executor->buildOperationList();
        $this->assertEquals(2, count($operations));
        $this->assertEquals($operations[0]->getPackageName(), 'foo');
        $this->assertEquals($operations[0]->getBundleFqcn(), 'Foo');
        $this->assertEquals($operations[0]->getPackageType(), 'claroline-core');
        $this->assertEquals($operations[0]->getType(), Operation::UPDATE);
        $this->assertEquals($operations[0]->getFromVersion(), '1.0.0.0');
        $this->assertEquals($operations[0]->getToVersion(), '1.2.0.0');
        $this->assertEquals($operations[1]->getPackageName(), 'quz');
        $this->assertEquals($operations[1]->getBundleFqcn(), 'Quz');
        $this->assertEquals($operations[1]->getPackageType(), 'claroline-plugin');
        $this->assertEquals($operations[1]->getType(), Operation::INSTALL);
    }

    public function missingRepoProvider()
    {
        return $this->buildRepoPaths([
            ['empty', 'non-existent'],
            ['non-existent', 'empty']
        ]);
    }

    public function notJsonRepoProvider()
    {
        return $this->buildRepoPaths([
            ['empty', 'not-json'],
            ['not-json', 'empty']
        ]);
    }

    public function notArrayRepoProvider()
    {
        return $this->buildRepoPaths([
            ['empty', 'not-array'],
            ['not-array', 'empty']
        ]);
    }

    private function mock($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function buildRepoPaths(array $nameRows)
    {
        return array_map(function ($row) {
            return array_map(function ($name) {
                return $this->repo($name);
            }, $row);
        }, $nameRows);
    }

    private function repo($name)
    {
        return __DIR__ . '/../../../Stub/repo/' . $name . '.json';
    }

    private function mockBundle($fqcn)
    {
        $bundle = $this->mock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $bundle->expects($this->any())->method('getNamespace')->willReturn('');
        $bundle->expects($this->any())->method('getName')->willReturn($fqcn);

        return $bundle;
    }

}
