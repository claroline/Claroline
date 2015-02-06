<?php

namespace Icap\PortfolioBundle\Manager;

use Claroline\CoreBundle\Entity\user;
use Doctrine\Common\Persistence\ObjectManager;
use Icap\PortfolioBundle\Importer\Importer;
use Icap\PortfolioBundle\Importer\ImporterInterface;
use Icap\PortfolioBundle\Importer\Leap2aImporter;

class ImportManager
{
    /**
     * @var \Icap\PortfolioBundle\Importer\ImporterInterface[]
     */
    private $importers;

    /**
     * @var array
     */
    private $availableFormats = [];

    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @param \Icap\PortfolioBundle\Importer\ImporterInterface[] $importers
     */
    public function __construct(array $importers = [])
    {
        if (0 === count($importers)) {
            $importers = [
                new Leap2aImporter()
            ];
        }

        foreach ($importers as $importer) {
            $this->importers[$importer->getFormat()] = $importer;
            $this->availableFormats[$importer->getFormat()] = $importer->getFormatLabel();
        }
    }

    /**
     * @return array
     */
    public function getAvailableFormats()
    {
        return $this->availableFormats;
    }

    /**
     * @return ObjectManager
     * @throws \Exception
     */
    public function getEntityManager()
    {
        if (null === $this->entityManager) {
            throw new \Exception('No entity manager, you can only simulate an import.');
        }
        return $this->entityManager;
    }

    /**
     * @param ObjectManager $entityManager
     *
     * @return ImportManager
     */
    public function setEntityManager(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * @param string $content
     * @param user   $user
     * @param string $format
     *
     * @return \Icap\PortfolioBundle\Entity\Portfolio|null
     * @throws \Exception
     */
    protected function import($content, User $user, $format)
    {
        if (!isset($this->importers[$format])) {
            throw new \Exception(sprintf("No importer for the '%s' format.", $format));
        }

        return $this->importers[$format]->import($content, $user);
    }

    /**
     * @param string $content
     * @param user   $user
     * @param string $format
     *
     * @return \Icap\PortfolioBundle\Entity\Portfolio|null
     * @throws \Exception
     */
    public function simulateImport($content, User $user, $format)
    {
        return $this->import($content, $user, $format);
    }

    /**
     * @param string $content
     * @param user   $user
     * @param string $format
     *
     * @return \Icap\PortfolioBundle\Entity\Portfolio|null
     * @throws \Exception
     */
    public function doImport($content, User $user, $format)
    {
        $portfolio     = $this->simulateImport($content, $user, $format);
        $entityManager = $this->getEntityManager();

        $entityManager->persist($portfolio);
        $entityManager->flush();

        return $portfolio;
    }
}
