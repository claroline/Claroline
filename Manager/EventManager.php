<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    private $translator;

    /**
     * @DI\InjectParams({
     *      "kernel" = @DI\Inject("kernel"),
     *      "translator" = @DI\Inject("translator")
     * }) 
     */
    public function __construct($kernel, $translator)
    {
        $this->kernel = $kernel;
        $this->translator = $translator;
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
            else {
                $classConstants  = $reflectionClass->getConstants();

                foreach ($classConstants as $key => $classConstant) {
                    if (preg_match('/^ACTION/', $key)) {
                        $constants[] = $classConstant;
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

        $sortedEvents = array();
        $genericResourceEvents = array();
        $genericResourceEvents['all'] = 'all';
        $sortedEvents['all'] = $this->translator->trans('all', array(), 'log');
        $tempResourceEvents = array();

        foreach ($textEvents as $textEvent) {
            $explodeTextEvents = explode('-', $textEvent);
            if ($explodeTextEvents[0]=='resource') {
                //$sortedEvents[$this->translator->trans($explodeTextEvents[0], array(), 'platform')][$this->translator->trans('all', array(), 'log')][$explodeTextEvents[0]] = $this->translator->trans('all', array(), 'log');
                $tempResourceEvents['all'][$explodeTextEvents[0]]=$this->translator->trans('all', array(), 'log');
            }
            else {
                $sortedEvents[$this->translator->trans($explodeTextEvents[0], array(), 'platform')][$explodeTextEvents[0]] = $this->translator->trans('all', array(), 'log');
            }            
            
            if ($explodeTextEvents[0]=='resource') {
                if(isset($explodeTextEvents[2])){
                    //$sortedEvents[$this->translator->trans($explodeTextEvents[0], array(), 'platform')][$this->translator->trans($explodeTextEvents[1], array(), 'resource')][$explodeTextEvents[0] . '-[[' . $explodeTextEvents[1] . ']]-all'] = $this->translator->trans('all', array(), 'log');
                    //$sortedEvents[$this->translator->trans($explodeTextEvents[0], array(), 'platform')][$this->translator->trans($explodeTextEvents[1], array(), 'resource')][$explodeTextEvents[0] . '-' . $explodeTextEvents[1] . '-' . $explodeTextEvents[2]] = $this->translator->trans('log_' . $explodeTextEvents[0] . '-' . $explodeTextEvents[1] . '-' . $explodeTextEvents[2] . '_filter', array(), 'log');
                    $tempResourceEvents[$explodeTextEvents[1]][$explodeTextEvents[0] . '-' . $explodeTextEvents[1] . '-' . $explodeTextEvents[2]] = $this->translator->trans('log_' . $explodeTextEvents[0] . '-' . $explodeTextEvents[1] . '-' . $explodeTextEvents[2] . '_filter', array(), 'log');
                }
                else {                    
                    $genericResourceEvents[$explodeTextEvents[1]] = $explodeTextEvents[0] . '-' . $explodeTextEvents[1]; 
                }
            }
            else if (isset($explodeTextEvents[2])) {
                $sortedEvents[$this->translator->trans($explodeTextEvents[0], array(), 'platform')][$explodeTextEvents[0] . '-' . $explodeTextEvents[1] . '-' . $explodeTextEvents[2]] = $this->translator->trans('log_' . $explodeTextEvents[0] . '-' . $explodeTextEvents[1] . '-' . $explodeTextEvents[2] . '_filter', array(), 'log');
            }
            else {
                $sortedEvents[$this->translator->trans($explodeTextEvents[0], array(), 'platform')][$explodeTextEvents[0] . '-' . $explodeTextEvents[1]] = $this->translator->trans('log_' . $explodeTextEvents[0] . '-' . $explodeTextEvents[1] . '_filter', array(), 'log');
            }
        }

        foreach ($tempResourceEvents as $sortedKey => $sortedEvent) {
            foreach ($genericResourceEvents as $genericKey => $genericEvent) {
                if ($sortedKey !== 'all'){
                    $sortedEvents[$this->translator->trans('resource', array(), 'platform')][$this->translator->trans($sortedKey, array(), 'resource')]['[[' . $sortedKey . ']]' . $genericEvent] = $this->translator->trans(($genericEvent === 'all')?$genericEvent:('log_' . $genericEvent . '_filter'), array(), 'log');
                }
                else {
                    $sortedEvents[$this->translator->trans('resource', array(), 'platform')][$this->translator->trans($sortedKey, array(), 'log')][$genericEvent] = $this->translator->trans(($genericEvent === 'all')?$genericEvent:('log_' . $genericEvent . '_filter'), array(), 'log');
                }                
            }
            if ($sortedKey !== 'all'){
                foreach ($tempResourceEvents[$sortedKey] as $resourceEventKey => $resourceEventValue) {
                    $sortedEvents[$this->translator->trans('resource', array(), 'platform')][$this->translator->trans($sortedKey, array(), 'resource')]['null'] = null;
                    $sortedEvents[$this->translator->trans('resource', array(), 'platform')][$this->translator->trans($sortedKey, array(), 'resource')][$resourceEventKey] = $resourceEventValue;
                }
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
