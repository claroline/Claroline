<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 16/11/17
 * Time: 15:45.
 */

namespace Icap\BibliographyBundle\API\Serializer;

use Claroline\CoreBundle\API\SerializerProvider;
use Icap\BibliographyBundle\Entity\BookReference;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.book_reference")
 * @DI\Tag("claroline.serializer")
 */
class BookReferenceSerializer
{
    /**
     * GroupSerializer constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getClass()
    {
        return 'Icap\BibliographyBundle\Entity\BookReference';
    }

    /**
     * Serializes a Book reference entity.
     *
     * @param BookReference $bookReference
     * @param array         $options
     *
     * @return array
     */
    public function serialize(BookReference $bookReference, array $options = [])
    {
        return [
            'id' => $bookReference->getId(),
            'author' => $bookReference->getAuthor(),
            'abstract' => $bookReference->getAbstract(),
            'isbn' => $bookReference->getIsbn(),
            'publisher' => $bookReference->getPublisher(),
            'printer' => $bookReference->getPrinter(),
            'publicationYear' => $bookReference->getPublicationYear(),
            'language' => $bookReference->getLanguage(),
            'pages' => $bookReference->getPageCount(),
            'url' => $bookReference->getUrl(),
            'cover' => $bookReference->getCoverUrl(),
        ];
    }

    /**
     * Deserializes data into a Group entity.
     *
     * @param array         $data
     * @param BookReference $bookReference
     * @param array         $options
     *
     * @return BookReference
     */
    public function deserialize($data, BookReference $bookReference = null, array $options = [])
    {
        $data = json_decode(json_encode($data), true);

        $bookReference->setAuthor($data['author']);
        $bookReference->setIsbn($data['isbn']);
        if (isset($data['abstract'])) {
            $bookReference->setAbstract($data['abstract']);
        }
        if (isset($data['publisher'])) {
            $bookReference->setPublisher($data['publisher']);
        }
        if (isset($data['printer'])) {
            $bookReference->setPrinter($data['printer']);
        }
        if (isset($data['publicationYear'])) {
            $bookReference->setPublicationYear($data['publicationYear']);
        }
        if (isset($data['language'])) {
            $bookReference->setLanguage($data['language']);
        }
        if (isset($data['pages'])) {
            $bookReference->setPageCount($data['pages']);
        }
        if (isset($data['url'])) {
            $bookReference->setUrl($data['url']);
        }
        if (isset($data['cover'])) {
            $bookReference->setCoverUrl($data['cover']);
        }

        return $bookReference;
    }
}
