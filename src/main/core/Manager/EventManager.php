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
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;

class EventManager
{
    private $kernel;
    private $om;
    private $translator;

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
    private function getEvents($restriction = null)
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
     * @param string $bundleNamespace
     * @param string $suffixLogNamespace
     * @param string $restriction
     *
     * @return array
     */
    private function getEventsByBundle(Finder $finder, $bundleNamespace, $suffixLogNamespace, $restriction)
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
    private function getActionConstantsForClass($classNamespace, $restriction)
    {
        $constants = [];
        /** @var \Claroline\CoreBundle\Event\Log\LogGenericEvent $reflectionClass */
        $reflectionClass = new \ReflectionClass($classNamespace);

        if (!$reflectionClass->isAbstract()) {
            $restrictions = $classNamespace::getRestriction();

            if ($restriction
                && $restrictions
                && 1 === count($restrictions)
                && (LogGenericEvent::DISPLAYED_ADMIN === $restrictions[0]
                    || LogGenericEvent::PLATFORM_EVENT_TYPE === $restrictions[0])
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
     * Gets formatted events for API filter.
     *
     * @param string|null $restriction
     *
     * @return array
     */
    public function getEventsForApiFilter($restriction = null)
    {
        $resourceOption = 'resource';
        $allOption = 'all';
        $eventNames = $this->getEvents($restriction);
        sort($eventNames);
        $sortedEvents = ['all' => 'all'];
        $genericResourceEvents = ['all' => 'all'];
        $tempResourceEvents = ['all' => []];

        foreach ($eventNames as $eventName) {
            $eventNameChunks = explode('-', $eventName);
            $eventKey = "log_${eventName}_filter";

            if ('clacoformbundle' !== $eventNameChunks[0] && !isset($sortedEvents[$eventNameChunks[0]])) {
                $sortedEvents[$eventNameChunks[0]] = ['all' => "${eventNameChunks[0]}::all"];
            }

            if ($resourceOption === $eventNameChunks[0]) {
                if (isset($eventNameChunks[2])) {
                    $tempResourceEvents[$eventNameChunks[1]][$eventKey] = $eventName;
                } else {
                    $genericResourceEvents[$eventKey] = $eventName;
                }
            } elseif ('clacoformbundle' === $eventNameChunks[0]) {
                $tempResourceEvents[$eventNameChunks[0]][$eventKey] = $eventName;
            } else {
                $sortedEvents[$eventNameChunks[0]][$eventKey] = $eventName;
            }
        }

        // adding resource types that don't define specific event classes
        $sortedEvents[$resourceOption][$allOption] = [];
        $remainingTypes = $this->om
            ->getRepository(ResourceType::class)
            ->findTypeNamesNotIn(array_keys($tempResourceEvents));

        foreach ($remainingTypes as $type) {
            $tempResourceEvents[$type['name']] = [];
        }

        foreach (array_keys($tempResourceEvents) as $resourceType) {
            if ('resource_shortcut' === $resourceType) {
                continue;
            }

            foreach ($genericResourceEvents as $genericEventKey => $genericEventName) {
                $eventPrefix = '';
                if ($allOption !== $resourceType) {
                    $eventPrefix = "${resourceOption}::${resourceType}::";
                }
                if ($allOption === $resourceType && $genericEventName === $allOption) {
                    $eventPrefix = "${resourceOption}::";
                }
                $sortedEvents[$resourceOption][$resourceType][$genericEventKey] = $eventPrefix.$genericEventName;
            }

            if ($allOption !== $resourceType) {
                foreach ($tempResourceEvents[$resourceType] as $resourceEventKey => $resourceEventName) {
                    $sortedEvents[$resourceOption][$resourceType][$resourceEventKey] = $resourceEventName;
                }
            }
        }

        return $this->formatEventsTableForApi($sortedEvents);
    }

    private function formatEventsTableForApi($events)
    {
        $formatedEvents = [];
        foreach ($events as $key => $value) {
            $formatedEvents[] = $this->formatEventEntryForApi($key, $value);
        }

        return $formatedEvents;
    }

    private function formatEventEntryForApi($key, $value)
    {
        return [
            'label' => $this->translator->trans($key, [], 'resource'),
            'value' => is_string($value) ? $value : uniqid('group', true),
            'choices' => is_array($value) ? $this->formatEventsTableForApi($value) : [],
        ];
    }
}
