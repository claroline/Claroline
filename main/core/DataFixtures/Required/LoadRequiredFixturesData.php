<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadRequiredFixturesData extends AbstractFixture implements ContainerAwareInterface
{
    use LoggableTrait;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function getDir()
    {
        $reflector = new \ReflectionClass(get_class($this));
        $filename = $reflector->getFileName();

        return dirname($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $fixturesDir = $this->getDir().DIRECTORY_SEPARATOR.'Data';
        $om = $this->container->get('claroline.persistence.object_manager');

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fixturesDir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (($file->getBasename('.php')) === $file->getBasename()) {
                continue;
            }
            $sourceFile = realpath($file->getPathName());
            require_once $sourceFile;
            $includedFiles[] = $sourceFile;
        }

        $declared = get_declared_classes();
        $orderedClassNames = [];
        $unorderedClassNames = [];

        foreach ($declared as $className) {
            $reflClass = new \ReflectionClass($className);
            $sourceFile = $reflClass->getFileName();

            if (in_array($sourceFile, $includedFiles) &&
                in_array(
                    'Claroline\CoreBundle\DataFixtures\Required\RequiredFixture',
                    $reflClass->getInterfaceNames()
                )
            ) {
                $fixture = new $className();

                if (method_exists($fixture, 'getOrder')) {
                    $order = $fixture->getOrder();

                    if (!isset($orderedClassNames[$order])) {
                        $orderedClassNames[$order] = $className;
                    } else {
                        throw new \Exception('Order '.$order.' is already defined');
                    }
                } else {
                    $unorderedClassNames[] = $className;
                }
            }
        }

        ksort($orderedClassNames);
        $orderedClassNames = array_merge($unorderedClassNames, $orderedClassNames);

        foreach ($orderedClassNames as $className) {
            $fixture = new $className();

            $order = 'none';

            if (method_exists($fixture, 'getOrder')) {
                $order = $fixture->getOrder();
            }

            $this->log('load '.$className.' position: '.$order);
            $fixture->setContainer($this->container);
            $fixture->load($om);
            $om->flush();
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
