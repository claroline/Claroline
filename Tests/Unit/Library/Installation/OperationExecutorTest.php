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

use Composer\Json\JsonFile;
use Composer\Package\Package;
use Composer\Repository\InstalledFilesystemRepository;
use org\bovigo\vfs\vfsStream;

class OperationExecutorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OperationExecutor
     */
    private $executor;
    private $kernel;
    private $baseInstaller;
    private $pluginInstaller;
    private $detector;

    protected function setUp()
    {
        $this->kernel = $this->mock('Symfony\Component\HttpKernel\KernelInterface');
        $this->baseInstaller = $this->mock('Claroline\InstallationBundle\Manager\InstallationManager');
        $this->pluginInstaller = $this->mock('Claroline\CoreBundle\Library\Installation\Plugin\Installer');
        $this->detector = $this->mock('Claroline\BundleRecorder\Detector\Detector');
        $this->executor = new OperationExecutor($this->kernel, $this->baseInstaller, $this->pluginInstaller);
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
     * @expectedException \RuntimeException
     * @expectedExceptionCode 123
     */
    public function testBuildOperationListThrowsIfRepoIsMissing($previous, $installed)
    {
        $this->executor->setRepositoryFiles($previous, $installed);
        $this->executor->buildOperationList();
    }

    /**
     * @dataProvider notJsonRepoProvider
     * @expectedException \Composer\Repository\InvalidRepositoryException
     */
    public function testBuildOperationListExecutorThrowsIfRepoIsNotJson($previous, $installed)
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
        $this->assertEquals($operations[0]->getPackage()->getName(), 'foo');
        $this->assertEquals($operations[0]->getBundleFqcn(), 'Foo');
        $this->assertEquals($operations[0]->getPackage()->getType(), 'claroline-core');
        $this->assertEquals($operations[0]->getType(), Operation::INSTALL);
        $this->assertEquals($operations[1]->getPackage()->getName(), 'bar');
        $this->assertEquals($operations[1]->getBundleFqcn(), 'Bar');
        $this->assertEquals($operations[1]->getPackage()->getType(), 'claroline-plugin');
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
        $this->assertEquals($operations[0]->getPackage()->getName(), 'foo');
        $this->assertEquals($operations[0]->getBundleFqcn(), 'Foo');
        $this->assertEquals($operations[0]->getPackage()->getType(), 'claroline-core');
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
        $this->assertEquals($operations[0]->getPackage()->getName(), 'foo');
        $this->assertEquals($operations[0]->getBundleFqcn(), 'Foo');
        $this->assertEquals($operations[0]->getPackage()->getType(), 'claroline-core');
        $this->assertEquals($operations[0]->getType(), Operation::UPDATE);
        $this->assertEquals($operations[0]->getFromVersion(), '1.0.0.0');
        $this->assertEquals($operations[0]->getToVersion(), '1.2.0.0');
        $this->assertEquals($operations[1]->getPackage()->getName(), 'quz');
        $this->assertEquals($operations[1]->getBundleFqcn(), 'Quz');
        $this->assertEquals($operations[1]->getPackage()->getType(), 'claroline-plugin');
        $this->assertEquals($operations[1]->getType(), Operation::INSTALL);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionCode 123
     */
    public function testExecuteThrowsIfPreviousRepoDoesNotExist()
    {
        $this->executor->setRepositoryFiles('/does/not/exist', '/either');
        $this->executor->execute([]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionCode 456
     */
    public function testExecuteThrowsIfPreviousRepoIsNotWritable()
    {
        vfsStream::setup('root');
        $previous = vfsStream::url('root/previous-installed.json');
        file_put_contents($previous, '[]');
        chmod($previous, 0444);
        $this->executor->setRepositoryFiles($previous, '/does/not/exist');
        $this->executor->execute([]);
    }

    public function testExecuteRemovesPreviousRepoWhenNoOperationRemain()
    {
        vfsStream::setup('root');
        $previous = vfsStream::url('root/previous-installed.json');
        file_put_contents($previous, '[]');
        $this->executor->setRepositoryFiles($previous, '/does/not/exist');

        $this->kernel->expects($this->once())->method('getBundles')->willReturn([]);

        $this->executor->execute([]);

        $this->assertFalse(file_exists($previous));
    }

    public function testExecuteCallsInstallersAndUpdatePreviousRepo()
    {
        vfsStream::setup('root');
        $previous = vfsStream::url('root/previous-installed.json');
        file_put_contents($previous, file_get_contents($this->repo('repo-1')));
        $this->executor->setRepositoryFiles($previous, '/does/not/exist');

        $this->kernel->expects($this->once())->method('getBundles')->willReturn([
            $this->mockBundle('Foo'),
            $this->mockBundle('Quz', true),
            $this->mockBundle('Bar', true)
        ]);

        $this->baseInstaller->expects($this->once())
            ->method('install')
            ->will($this->throwException(new \Exception('from test')));

        $installOp1 = new Operation(Operation::INSTALL, $this->package('quz', '3.4.1.2', 'claroline-plugin'), 'Quz');
        $updateOp = new Operation(Operation::UPDATE, $this->package('bar', '2.0.0.0', 'claroline-plugin'), 'Bar');
        $updateOp->setFromVersion('2.0.0.0');
        $updateOp->setToVersion('3.0.0.0');
        $installOp2 = new Operation(Operation::INSTALL, $this->package('foo', '5.2.3.1', 'claroline-core'), 'Foo');

        try {
            $this->executor->execute([$installOp1, $updateOp, $installOp2]);
            $this->fail('An exception should have been thrown');
        } catch (\Exception $ex) {
            $this->assertTrue(file_exists($previous));
            $repo = new InstalledFilesystemRepository(new JsonFile($previous));
            $this->assertNotNull($repo->findPackage('quz', '3.4.1.2'));
            $this->assertNotNull($repo->findPackage('bar', '2.0.0.0'));
        }
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

    private function mockBundle($fqcn, $plugin = false)
    {
        $class = $plugin ?
            'Claroline\CoreBundle\Library\PluginBundleInterface' :
            'Claroline\InstallationBundle\Bundle\InstallableInterface';
        $bundle = $this->mock($class);
        $bundle->expects($this->any())->method('getNamespace')->willReturn('');
        $bundle->expects($this->any())->method('getName')->willReturn($fqcn);

        return $bundle;
    }

    private function package($name, $version, $type)
    {
        $package = new Package($name, $version, 'not-important');
        $package->setType($type);

        return $package;
    }
}
