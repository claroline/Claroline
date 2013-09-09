<?php
namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Claroline\CoreBundle\Form\DataTransformer\DateRangeToTextTransformer;
use Claroline\CoreBundle\Form\Log\WorkspaceLogFilterType;
use Claroline\CoreBundle\Form\Log\AdminLogFilterType;
use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Claroline\CoreBundle\Entity\Log\LogWorkspaceWidgetConfig;
use Claroline\CoreBundle\Entity\Log\LogDesktopWidgetConfig;

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
     * @return array
     */
    public function getEvents()
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
                $events = array_merge($events, $this->getEventsByBundle($finder, $bundle->getNamespace(), $suffixLogNamespace));
            }
        }

        return $events;
    }

    /**
     * @param Finder $finder
     * @param string $bundleNamespace
     * @param string $suffixLogNamespace
     *
     * @return array
     */
    protected function getEventsByBundle(Finder $finder, $bundleNamespace, $suffixLogNamespace)
    {
        $events = array();

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $classNamespace = $bundleNamespace . $suffixLogNamespace . '\\' . $file->getBasename('.' . $file->getExtension());
            if (in_array('Claroline\CoreBundle\Event\Log\LogGenericEvent', class_parents($classNamespace))) {
                $events = array_merge($events, $this->getActionConstantsforClass($classNamespace));
            }
        }

        return $events;
    }

    /**
     * @param string $classNamespace
     *
     * @return array
     */
    protected function getActionConstantsforClass($classNamespace)
    {
        $constants       = array();
        $reflectionClass = new \ReflectionClass($classNamespace);
        $classConstants  = $reflectionClass->getConstants();

        foreach ($classConstants as $key => $classConstant) {
            if (preg_match('/^ACTION/', $key)) {
                $constants[] = $classConstant;
            }
        }

        return $constants;
    }

    /**
     * @return array
     */
    public function getSortedEventsForFilter()
    {
        $textEvents   = $this->getEvents();

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
}
