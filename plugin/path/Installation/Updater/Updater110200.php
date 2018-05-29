<?php

namespace Innova\PathBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Manager\Resource\MaskManager;
use Claroline\InstallationBundle\Updater\Updater;
use Innova\PathBundle\Entity\SecondaryResource;
use Innova\PathBundle\Entity\Step;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater110200 extends Updater
{
    /** @var ContainerInterface */
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->updateSteps();
        $this->cleanMasks();
    }

    /**
     * Removes unused mask decoder.
     */
    private function cleanMasks()
    {
        /** @var ObjectManager $om */
        $om = $this->container->get('claroline.persistence.object_manager');

        /** @var MaskManager $maskManager */
        $maskManager = $this->container->get('claroline.manager.mask_manager');

        /** @var ResourceType $pathType */
        $pathType = $om
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(['name' => 'innova_path']);

        $this->log('Removing unused mask decoder `path_administrate`...');
        $maskManager->removeMask($pathType, 'path_administrate');

        $this->log('Removing unused mask decoder `manageresults`...');
        $maskManager->removeMask($pathType, 'manageresults');

        $om->flush();
    }

    /**
     * Initializes Steps resource node from activity resource.
     */
    private function updateSteps()
    {
        $this->log('Initializing resource node of steps...');

        /** @var ObjectManager $om */
        $om = $this->container->get('claroline.persistence.object_manager');
        $steps = $om->getRepository('Innova\PathBundle\Entity\Step')->findAll();

        $om->startFlushSuite();
        $i = 0;

        /** @var Step $step */
        foreach ($steps as $step) {
            /** @var Activity $activity */
            $activity = $step->getActivity();

            if (!empty($activity)) {
                if (!empty($activity->getPrimaryResource())) {
                    $step->setResource($activity->getPrimaryResource());
                }
                $step->setTitle($activity->getResourceNode()->getName());
                $step->setDescription($activity->getDescription());

                $parameters = $activity->getParameters();

                if (!empty($parameters)) {
                    $order = 0;

                    foreach ($parameters->getSecondaryResources() as $resource) {
                        $secondaryResource = new SecondaryResource();
                        $secondaryResource->setResource($resource);
                        $secondaryResource->setOrder($order);
                        $step->addSecondaryResource($secondaryResource);
                        $om->persist($secondaryResource);
                        ++$order;
                    }
                }
                $om->persist($step);
            }
            ++$i;

            if ($i % 250 === 0) {
                $om->forceFlush();
            }
        }

        $om->endFlushSuite();
    }
}
