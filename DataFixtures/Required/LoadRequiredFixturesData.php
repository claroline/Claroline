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

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadRequiredFixturesData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $fixturesDir = __DIR__ . DIRECTORY_SEPARATOR . 'Data';
        $om = $this->container->get('claroline.persistence.object_manager');
        //$om->startFlushSuite();

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fixturesDir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (($fileName = $file->getBasename('.php')) == $file->getBasename()) {
                continue;
            }
            $sourceFile = realpath($file->getPathName());
            require_once $sourceFile;
            $includedFiles[] = $sourceFile;
        }

        $declared = get_declared_classes();
        $orderedClassNames = array();
        $unorderedClassNames = array();

        foreach ($declared as $className) {
            $reflClass = new \ReflectionClass($className);
            $sourceFile = $reflClass->getFileName();

            if (in_array($sourceFile, $includedFiles) &&
                in_array(
                    'Claroline\CoreBundle\DataFixtures\Required\RequiredFixture',
                    $reflClass->getInterfaceNames()
                )
            ) { 
                $fixture = new $className;

                if (method_exists($fixture, 'getOrder')) {
                    $order = $fixture->getOrder();
                    
                    if (!isset($orderedClassNames[$order])) {
                        $orderedClassNames[$order] = $className;
                    }
                    else {
                        $orderedClassNames[] = $className;
                    }
                } else {
                    $unorderedClassNames[] = $className;
                }
            }
        }
        ksort($orderedClassNames);
        
        foreach ($unorderedClassNames as $className) {
            $orderedClassNames[] = $className;
        }
        
        foreach ($orderedClassNames as $className) {
            $fixture = new $className;
            $fixture->setContainer($this->container);
            $fixture->load($om);
            $om->flush();
        }

        //$om->endFlushSuite();

        //create the default workspace template.
        $destinationPath = $this->container->getParameter('claroline.param.templates_directory'). '/default.zip';
        $sourcePath = $this->container->getParameter('claroline.param.default_template');
        @unlink($destinationPath);
        copy($sourcePath, $destinationPath);
    }
}
