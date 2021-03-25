<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 16/11/17
 * Time: 15:45.
 */

namespace Icap\BibliographyBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Icap\BibliographyBundle\Entity\BookReference;

class BookReferenceSerializer
{
    use SerializerTrait;

    public function getClass()
    {
        return BookReference::class;
    }

    public function getName()
    {
        return 'book_reference';
    }

    /**
     * Serializes a Book reference entity.
     *
     * @return array
     */
    public function serialize(BookReference $bookReference, array $options = [])
    {
        return [
            'id' => $bookReference->getUuid(),
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
     *
     * @return BookReference
     */
    public function deserialize($data, BookReference $bookReference = null, array $options = [])
    {
        if (empty($bookReference)) {
            $bookReference = new BookReference();
            $bookReference->refreshUuid();
        }

        $this->sipe('author', 'setAuthor', $data, $bookReference);
        $this->sipe('isbn', 'setIsbn', $data, $bookReference);
        $this->sipe('abstract', 'setAbstract', $data, $bookReference);
        $this->sipe('publisher', 'setPublisher', $data, $bookReference);
        $this->sipe('printer', 'setPrinter', $data, $bookReference);
        $this->sipe('publicationYear', 'setPublicationYear', $data, $bookReference);
        $this->sipe('language', 'setLanguage', $data, $bookReference);
        $this->sipe('pages', 'setPageCount', $data, $bookReference);
        $this->sipe('url', 'setUrl', $data, $bookReference);
        $this->sipe('cover', 'setCoverUrl', $data, $bookReference);

        return $bookReference;
    }
}
