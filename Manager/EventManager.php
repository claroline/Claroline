<?php
namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Finder\Finder;

/**
 * @DI\Service("claroline.event.manager")
 */
class EventManager
{
    /**
     * @var \AppKernel
     */
    private $kernel;

    /**
     * @DI\InjectParams({
     *     "kernel" = @DI\Inject("kernel")
     * })
     */
    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Get all existing event name with their associated label
     *
     * @param string|null $restiction
     *
     * @return array
     */
    public function getEvents($restiction = null)
    {
        $suffixLogPath      = '/Event/Log';
        $suffixLogNamespace = '\Event\Log';
        $bundles            = $this->kernel->getBundles();
        $events          = array();

        foreach ($bundles as $bundle) {
            $bundleEventLogDirectory = $bundle->getPath() . $suffixLogPath;
            if (file_exists($bundleEventLogDirectory)) {
                $finder = new Finder();
                $finder->files()->in($bundleEventLogDirectory)->sortByName();
                $events = array_merge($events, $this->getEventsByBundle($finder, $bundle->getNamespace(), $suffixLogNamespace, $restiction));
            }
        }

        return $events;
    }

    /**
     * @param Finder $finder
     * @param string $bundleNamespace
     * @param string $suffixLogNamespace
     * @param string $restriction
     *
     * @return array
     */
    protected function getEventsByBundle(Finder $finder, $bundleNamespace, $suffixLogNamespace, $restriction)
    {
        $events = array();

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $classNamespace = $bundleNamespace . $suffixLogNamespace . '\\' . $file->getBasename('.' . $file->getExtension());
            if (in_array('Claroline\CoreBundle\Event\Log\LogGenericEvent', class_parents($classNamespace))) {
                $events = array_merge($events, $this->getActionConstantsforClass($classNamespace, $restriction));
            }
        }

        return $events;
    }

    /**
     * @param string $classNamespace
     * @param string $restriction
     *
     * @return array
     */
    protected function getActionConstantsforClass($classNamespace, $restriction)
    {
        $constants       = array();
        /** @var \Claroline\CoreBundle\Event\Log\LogGenericEvent $reflectionClass */ 
        $reflectionClass = new \ReflectionClass($classNamespace);
        if (!$reflectionClass->isAbstract()) {
            if (null !== $restriction) {
                $restrictions = $classNamespace::getRestriction();

                if (in_array($restriction, $restrictions)) {
                    $classConstants  = $reflectionClass->getConstants();

                    foreach ($classConstants as $key => $classConstant) {
                        if (preg_match('/^ACTION/', $key)) {
                            $constants[] = $classConstant;
                        }
                    }
                }
            }
        }

        return $constants;
    }

    /**
     * @param string|null $restriction
     *
     * @return array
     */
    public function getSortedEventsForFilter($restriction = null)
    {
        $textEvents = $this->getEvents($restriction);

        foreach ($textEvents as $textEvent) {
            $explodeTextEvents = explode('-', $textEvent);

            $sortedEvents[$explodeTextEvents[0]][$explodeTextEvents[0]] = 'all';
            if (isset($explodeTextEvents[2])) {
                $sortedEvents[$explodeTextEvents[0]][$explodeTextEvents[0] . '-' . $explodeTextEvents[1] . '-' . $explodeTextEvents[2]] = 'log_' . $explodeTextEvents[0] . '-' . $explodeTextEvents[1] . '-' . $explodeTextEvents[2] . '_filter';
            }
            else {
                $sortedEvents[$explodeTextEvents[0]][$explodeTextEvents[0] . '-' . $explodeTextEvents[1]] = 'log_' . $explodeTextEvents[0] . '-' . $explodeTextEvents[1] . '_filter';
            }
        }

        return $sortedEvents;
    }

    /**
     * @param string|null $restriction
     *
     * @return array
     */
    public function getSortedEventsForConfigForm($restriction = null)
    {
        $events = array();

        foreach ($this->getEvents($restriction) as $event) {
            $events[$event] = 'log_' . $event . '_title';
        }

        return $events;
    }
}
