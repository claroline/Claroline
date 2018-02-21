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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.event.manager")
 */
class EventManager
{
    private $kernel;
    private $om;
    private $translator;

    /**
     * @DI\InjectParams({
     *      "kernel"        = @DI\Inject("kernel"),
     *      "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *      "translator"    = @DI\Inject("translator")
     * })
     */
    public function __construct(
        KernelInterface $kernel,
        ObjectManager $om,
        TranslatorInterface $translator
    ) {
        $this->kernel = $kernel;
        $this->om = $om;
        $this->translator = $translator;
    }

    /**
     * Get all existing event name with their associated label.
     *
     * @param string|null $restriction
     *
     * @return array
     */
    public function getEvents($restriction = null)
    {
        $suffixLogPath = '/Event/Log';
        $suffixLogNamespace = '\Event\Log';
        $bundles = $this->kernel->getBundles();
        $events = [];

        foreach ($bundles as $bundle) {
            $bundleEventLogDirectory = $bundle->getPath().$suffixLogPath;
            if (file_exists($bundleEventLogDirectory)) {
                $finder = new Finder();
                $finder->files()->in($bundleEventLogDirectory)->sortByName();
                $events = array_merge(
                    $events,
                    $this->getEventsByBundle($finder, $bundle->getNamespace(), $suffixLogNamespace, $restriction)
                );
            }
        }

        return $events;
    }

    /**
     * Get all existing event name with their associated label.
     *
     * @param string|null $restriction
     * @param string      $resourceClass
     *
     * @return array
     */
    public function getEventsForBundle($restriction = null, $resourceClass)
    {
        $suffixLogPath = '/Event/Log';
        $suffixLogNamespace = '\Event\Log';
        $bundles = $this->kernel->getBundles();
        $events = [];

        foreach ($bundles as $bundle) {
            if (0 === strpos($resourceClass, $bundle->getNamespace())
                || 0 === strpos(get_class($this), $bundle->getNamespace())) {
                if (0 !== strpos(get_class($this), $bundle->getNamespace())) {
                    array_push($events, null);
                }
                $bundleEventLogDirectory = $bundle->getPath().$suffixLogPath;
                if (file_exists($bundleEventLogDirectory)) {
                    $finder = new Finder();
                    $finder->files()->in($bundleEventLogDirectory)->sortByName();
                    $events = array_merge(
                        $events,
                        $this->getEventsByBundle($finder, $bundle->getNamespace(), $suffixLogNamespace, $restriction)
                    );
                }
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
        $events = [];

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $classNamespace = $bundleNamespace
                .$suffixLogNamespace
                .'\\'
                .$file->getBasename('.'.$file->getExtension());

            if (in_array('Claroline\CoreBundle\Event\Log\LogGenericEvent', class_parents($classNamespace))) {
                $events = array_merge($events, $this->getActionConstantsForClass($classNamespace, $restriction));
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
    protected function getActionConstantsForClass($classNamespace, $restriction)
    {
        $constants = [];
        /** @var \Claroline\CoreBundle\Event\Log\LogGenericEvent $reflectionClass */
        $reflectionClass = new \ReflectionClass($classNamespace);

        if (!$reflectionClass->isAbstract()) {
            if ($restriction
                && ($restrictions = $classNamespace::getRestriction())
                && 1 === count($restrictions)
                && LogGenericEvent::DISPLAYED_ADMIN === $restrictions[0]
                && $restriction !== $restrictions[0]) {
                return $constants; // event is admin only
            }

            $classConstants = $reflectionClass->getConstants();

            foreach ($classConstants as $key => $classConstant) {
                if (preg_match('/^ACTION/', $key)) {
                    $constants[] = $classConstant;
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
        $allTranslatedText = $this->translator->trans('all', [], 'log');
        $sortedEvents = [];
        $genericResourceEvents = [];
        $genericResourceEvents['all'] = 'all';
        $sortedEvents[$allTranslatedText] = 'all';
        $tempResourceEvents = [];

        foreach ($textEvents as $textEvent) {
            $explodeTextEvents = explode('-', $textEvent);
            $shortTextEvent = $explodeTextEvents[0].'-'.$explodeTextEvents[1];
            $eventTrans = $this->translator->trans($explodeTextEvents[0], [], 'log');

            if ('resource' === $explodeTextEvents[0]) {
                $tempResourceEvents['all'][$allTranslatedText] = $explodeTextEvents[0];
            } else {
                $sortedEvents[$eventTrans][$explodeTextEvents[0].': '.$allTranslatedText] = $explodeTextEvents[0];
            }

            if ('resource' === $explodeTextEvents[0]) {
                if (isset($explodeTextEvents[2])) {
                    $tempResourceEvents[$explodeTextEvents[1]][$this->translator->trans(
                        'log_'.$textEvent.'_filter', [], 'log'
                    )] = $textEvent;
                } else {
                    $genericResourceEvents[$explodeTextEvents[1]] = $shortTextEvent;
                }
            } elseif (isset($explodeTextEvents[2])) {
                $sortedEvents[$eventTrans][$this->translator->trans(
                    'log_'.$textEvent.'_filter', [], 'log'
                )] = $textEvent;
            } else {
                $sortedEvents[$eventTrans][$this->translator->trans(
                    'log_'.$shortTextEvent.'_filter', [], 'log'
                )] = $shortTextEvent;
            }
        }

        $resourceTrans = $this->translator->trans('resource', [], 'platform');

        // adding resource types that don't define specific event classes
        $remainingTypes = $this->om
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findTypeNamesNotIn(array_keys($tempResourceEvents));

        foreach ($remainingTypes as $type) {
            $tempResourceEvents[$type['name']] = [];
        }

        foreach (array_keys($tempResourceEvents) as $sortedKey) {
            $keyTrans = $this->translator->trans($sortedKey, [], 'resource');

            foreach ($genericResourceEvents as $genericEvent) {
                $logTrans = $this->translator->trans(
                    'all' === $genericEvent ? $genericEvent : 'log_'.$genericEvent.'_filter',
                        [],
                        'log'
                    );

                $genericEvent = ('all' === $genericEvent) ? 'resource' : $genericEvent;

                if ('all' !== $sortedKey) {
                    $sortedEvents[$resourceTrans][$keyTrans][$keyTrans.': '.$logTrans] = '[['.$sortedKey.']]'.$genericEvent;
                } else {
                    $sortedEvents[$resourceTrans][$allTranslatedText]['resource: '.$logTrans] = $genericEvent;
                }
            }

            if ('all' !== $sortedKey) {
                foreach ($tempResourceEvents[$sortedKey] as $resourceEventKey => $resourceEventValue) {
                    $sortedEvents[$resourceTrans][$keyTrans][$resourceEventKey] = $resourceEventValue;
                }
            }
        }

        return $sortedEvents;
    }

    /**
     * @param string|null $restriction
     * @param string|null $resourceClass
     *
     * @return array
     */
    public function getResourceEventsForFilter($restriction = null, $resourceClass = null)
    {
        $textEvents = $this->getEventsForBundle($restriction, $resourceClass);
        $sortedEvents = [];
        $sortedEvents['all'] = 'all';
        foreach ($textEvents as $textEvent) {
            if (null === $textEvent) {
                $sortedEvents['null'] = null;
            } elseif (0 === strpos($textEvent, 'resource')) {
                $sortedEvents[$textEvent] = 'log_'.$textEvent.'_filter';
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
        $events = [];

        foreach ($this->getEvents($restriction) as $event) {
            $events[$event] = 'log_'.$event.'_title';
        }

        return $events;
    }
}
