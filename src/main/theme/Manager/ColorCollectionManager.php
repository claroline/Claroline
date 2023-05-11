<?php

namespace Claroline\ThemeBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\ThemeBundle\Entity\ColorCollection;
use Doctrine\ORM\EntityManagerInterface;

class ColorCollectionManager
{
    private ObjectManager $om;
    private EntityManagerInterface $entityManager;
    private PlatformConfigurationHandler $config;

    public function __construct(ObjectManager $om, EntityManagerInterface $entityManager, PlatformConfigurationHandler $config)
    {
        $this->om = $om;
        $this->entityManager = $entityManager;
        $this->config = $config;
    }

    public function getCurrentColorChart(): array
    {
        $setName = $this->config->getParameter('display.color_chart');
        return $this->om->getRepository(ColorCollection::class)->findOneBy(['name' => $setName]);
    }

    public function getAvailableColorCharts(): array
    {
        /** @var ColorCollection[] $sets */
        $sets = $this->om->getRepository(ColorCollection::class)->findAll();

        $available = [];
        foreach ($sets as $set) {
            if (empty($available[$set->getName()])) {
                $available[$set->getName()] = [
                    'name' => $set->getName(),
                    'colors' => $set->getColors(),
                ];
            }
        }

        return array_values($available);
    }

    public function createColorCollection(array $colorCollectionData): ColorCollection
    {
        $colorCollection = new ColorCollection();
        $colorCollection->setName($colorCollectionData['name']);
        $colorCollection->setColors( array_values( $colorCollectionData['colors'] ) );

        $this->entityManager->persist($colorCollection);
        $this->entityManager->flush();

        return $colorCollection;
    }

    public function updateColorCollection(ColorCollection $colorCollection, array $colorCollectionData): ColorCollection
    {
        if (isset($colorCollectionData['name'])) {
            $colorCollection->setName($colorCollectionData['name']);
        }

        if (isset($colorCollectionData['colors'])) {
            $colorCollection->setColors(array_values( $colorCollectionData['colors']));
        }

        $this->entityManager->flush();

        return $colorCollection;
    }

    public function deleteColorCollection(ColorCollection $colorCollection): void
    {
        $this->entityManager->remove($colorCollection);
        $this->entityManager->flush();
    }
}
