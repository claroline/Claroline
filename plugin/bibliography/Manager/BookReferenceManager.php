<?php

namespace Icap\BibliographyBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Icap\BibliographyBundle\Entity\BookReference;
use Icap\BibliographyBundle\Entity\BookReferenceConfiguration;
use Icap\BibliographyBundle\Repository\BookReferenceConfigurationRepository;
use Icap\BibliographyBundle\Repository\BookReferenceRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.bookReference.manager")
 */
class BookReferenceManager
{
    private $om;
    /** @var BookReferenceConfigurationRepository */
    protected $configRepository;
    /** @var BookReferenceRepository */
    protected $bookReferenceRepository;

    /**
     * @DI\InjectParams({
     *     "om"        = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->configRepository = $this->om->getRepository('IcapBibliographyBundle:BookReferenceConfiguration');
        $this->bookReferenceRepository = $this->om->getRepository('IcapBibliographyBundle:BookReference');
    }

    public function updateConfiguration(BookReferenceConfiguration $config, $postData)
    {
        try {
            $config->setApiKey($postData['api_key']);
            $this->om->persist($config);
            $this->om->flush();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getConfig()
    {
        $config = $this->configRepository->findAll()[0];

        return $config;
    }

    /**
     * Imports book reference object from array
     * (see BibliographyImporter for structure and description).
     *
     * @param array $data
     *
     * @return BookReference
     */
    public function import(array $data)
    {
        $bookReference = new BookReference();
        if (isset($data['data'])) {
            $bookReferenceData = $data['data'];

            $bookReference->setAuthor($bookReferenceData['author']);
            $bookReference->setDescription($bookReferenceData['description']);
            $bookReference->setAbstract($bookReferenceData['abstract']);
            $bookReference->setIsbn($bookReferenceData['isbn']);
            $bookReference->setPublisher($bookReferenceData['publisher']);
            $bookReference->setPrinter($bookReferenceData['printer']);
            $bookReference->setPublicationYear($bookReferenceData['publicationYear']);
            $bookReference->setLanguage($bookReferenceData['language']);
            $bookReference->setPageCount($bookReferenceData['pageCount']);
            $bookReference->setUrl($bookReferenceData['url']);
            $bookReference->setCoverUrl($bookReferenceData['coverUrl']);
        }

        $this->om->persist($bookReference);

        return $bookReference;
    }

    public function export(Workspace $workspace, array &$files, $object)
    {
        /* @var BookReference $object */
        return [
            'author' => $object->getAuthor(),
            'description' => $object->getDescription(),
            'abstract' => $object->getAbstract(),
            'isbn' => $object->getIsbn(),
            'publisher' => $object->getPublisher(),
            'printer' => $object->getPrinter(),
            'publicationYear' => $object->getPublicationYear(),
            'language' => $object->getLanguage(),
            'pageCount' => $object->getPageCount(),
            'url' => $object->getUrl(),
            'coverUrl' => $object->getCoverUrl(),
        ];
    }

    public function bookExistsInWorkspace($isbn, $workspace)
    {
        return $this->bookReferenceRepository->findOneByIsbnAndByWorkspace($isbn, $workspace);
    }

    public function isApiConfigured()
    {
        return $this->configRepository->isApiConfigured();
    }
}
