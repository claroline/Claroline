<?php

namespace Claroline\CoreBundle\Library\Installation;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class responsible for adding the entity mapping of a bundle to the default
 * entity manager, so that a new bundle can be registered and its entities can
 * be used in the same process, without rebuilding the whole container.
 *
 * @DI\Service("claroline.installation.mapping_loader")
 */
class MappingLoader
{
    private $container;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Adds the entity mapping of a bundle to the default entity manager.
     * Currently only the annotation driver is supported.
     *
     * @param \Symfony\Component\HttpKernel\Bundle\Bundle $bundle
     */
    public function registerMapping(Bundle $bundle)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $emConfig = $em->getConfiguration();
        $driverImpl = $emConfig->getMetadataDriverImpl();

        foreach ($driverImpl->getDrivers() as $driver) {
            if ($driver instanceof AnnotationDriver) {
                $driver->addPaths(array($bundle->getPath() . '/Entity'));
                $entityNamespace = $bundle->getNamespace() . '\Entity';
                $driverImpl->addDriver($driver, $entityNamespace);
                $namespaces = $emConfig->getEntityNamespaces();
                $namespaces[] = $entityNamespace;
                $emConfig->setEntityNamespaces($namespaces);
                break;
            }
        }

        $this->container->set('doctrine.orm.entity_manager', $em);
    }
}
