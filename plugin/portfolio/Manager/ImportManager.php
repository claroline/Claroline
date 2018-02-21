<?php

namespace Icap\PortfolioBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\user;
use Icap\PortfolioBundle\Importer\Importer;
use Icap\PortfolioBundle\Importer\Leap2aImporter;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_portfolio.manager.import")
 */
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
    private $objectManager;

    /**
     * @DI\InjectParams({
     *     "objectManager"      = @DI\Inject("claroline.persistence.object_manager"),
     *     "leap2aImporter"      = @DI\Inject("icap_portfolio.importer.leap2a")
     * })
     */
    public function __construct(
        ObjectManager $objectManager,
        Leap2aImporter $leap2aImporter
    ) {
        $importers = [$leap2aImporter];
        $this->objectManager = $objectManager;

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
     *
     * @throws \Exception
     */
    public function getObjectManager()
    {
        if (null === $this->objectManager) {
            throw new \Exception('No entity manager, you can only simulate an import.');
        }

        return $this->objectManager;
    }

    /**
     * @param string $content
     * @param user   $user
     * @param string $format
     *
     * @return \Icap\PortfolioBundle\Entity\Portfolio|null
     *
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
     *
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
     *
     * @throws \Exception
     */
    public function doImport($content, User $user, $format)
    {
        $portfolio = $this->simulateImport($content, $user, $format);
        $entityManager = $this->getObjectManager();

        foreach ($portfolio->getPortfolioWidgets() as $portfolioWidget) {
            $entityManager->persist($portfolioWidget->getWidget());
        }
        $entityManager->persist($portfolio);
        $entityManager->flush();

        return $portfolio;
    }
}
