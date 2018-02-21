<?php

namespace Icap\BibliographyBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Icap\BibliographyBundle\Entity\BookReference;
use Icap\BibliographyBundle\Entity\BookReferenceConfiguration;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("icap.bookReference.manager")
 */
class BookReferenceManager
{
    private $om;
    protected $container;
    protected $configRepository;
    protected $bookReferenceRepository;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *     "om"        = @DI\Inject("claroline.persistence.object_manager"),
     * })
     */
    public function __construct(ContainerInterface $container, ObjectManager $om)
    {
        $this->om = $om;
        $this->container = $container;
        $this->configRepository = $this->om->getRepository('IcapBibliographyBundle:BookReferenceConfiguration');
        $this->bookReferenceRepository = $this->om->getRepository('IcapBibliographyBundle:BookReference');
    }

    public function updateConfiguration(BookReferenceConfiguration $config, $postData)
    {
        try {
            $om = $this->container->get('claroline.persistence.object_manager');
            $config->setApiKey($postData['api_key']);
            $om->persist($config);
            $om->flush();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getConfig()
    {
        $config = $this->container->get('doctrine.orm.entity_manager');
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
}
