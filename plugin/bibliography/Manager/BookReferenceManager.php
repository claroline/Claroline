<?php

namespace Icap\BibliographyBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Icap\BibliographyBundle\Entity\BookReferenceConfiguration;
use Icap\BibliographyBundle\Repository\BookReferenceConfigurationRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.bookReference.manager")
 */
class BookReferenceManager
{
    /** @var ObjectManager */
    private $om;

    /** @var BookReferenceConfigurationRepository */
    private $configRepository;

    /**
     * BookReferenceManager constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->configRepository = $this->om->getRepository(BookReferenceConfiguration::class);
    }

    public function getConfig()
    {
        $config = $this->configRepository->findAll()[0];

        return $config;
    }

    public function isApiConfigured()
    {
        return $this->configRepository->isApiConfigured();
    }
}
