<?php

namespace Claroline\BundleRecorder;

use Composer\Package\PackageInterface;
use Claroline\BundleRecorder\Detector\Detector;
use Claroline\BundleRecorder\Handler\BundleHandler;
use Claroline\BundleRecorder\Handler\OperationHandler;
use Claroline\BundleRecorder\Handler\VersionHandler;
use Claroline\BundleRecorder\Operation;

class Recorder
{
    private $detector;
    private $bundleHandler;
    private $operationHandler;
    private $logger;

    public function __construct(
        Detector $detector,
        BundleHandler $bundleHandler,
        OperationHandler $operationHandler
    )
    {
        $this->detector = $detector;
        $this->bundleHandler = $bundleHandler;
        $this->operationHandler = $operationHandler;
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
    }

    public function record($operationType, PackageInterface $target, PackageInterface $initial = null)
    {
        if (!in_array($operationType, array(Operation::INSTALL, Operation::UPDATE, Operation::UNINSTALL))) {
            throw new \InvalidArgumentException(
                'Operation type must be a Operation::* class constant'
            );
        }

        if ($operationType === Operation::UPDATE && !$initial) {
            throw new \LogicException(
                'Update operation requires the initial package as third parameter'
            );
        }

        if ($target->getType() === 'claroline-core' || $target->getType() === 'claroline-plugin') {
            $bundle = $this->detector->detectBundle($target->getPrettyName());
            $type = $target->getType() === 'claroline-core' ?
                Operation::BUNDLE_CORE :
                Operation::BUNDLE_PLUGIN;
            $operation = new Operation($operationType, $bundle, $type);

            if ($operationType === Operation::UPDATE) {
                $operation->setFromVersion($initial->getVersion());
                $operation->setToVersion($target->getVersion());
            } else {
                $method = $operationType === Operation::INSTALL ? 'addBundles' : 'removeBundles';
                $this->bundleHandler->{$method}(array($bundle));
            }

            $this->operationHandler->addOperation($operation);
        } else {
            $bundles = $this->detector->detectBundles($target->getPrettyName());
            $method = $operationType === Operation::INSTALL ? 'addBundles' : 'removeBundles';
            $this->bundleHandler->{$method}($bundles);
        }
    }
}
