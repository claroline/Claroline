<?php

namespace Icap\BibliographyBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Icap\BibliographyBundle\Entity\BookReferenceConfiguration;

class BookReferenceConfigurationSerializer
{
    use SerializerTrait;

    public function getClass()
    {
        return BookReferenceConfiguration::class;
    }

    /**
     * Serializes a Book reference configuration entity.
     *
     * @param BookReferenceConfiguration $bookReferenceConfiguration
     * @param array                      $options
     *
     * @return array
     */
    public function serialize(BookReferenceConfiguration $bookReferenceConfiguration, array $options = [])
    {
        return [
            'id' => $bookReferenceConfiguration->getId(),
            'apiKey' => $bookReferenceConfiguration->getApiKey(),
        ];
    }

    public function getName()
    {
        return 'book_reference_configuration';
    }

    /**
     * De-serializes a book reference configuration.
     *
     * @param $data
     * @param BookReferenceConfiguration|null $bookReferenceConfiguration
     * @param array                           $options
     *
     * @return BookReferenceConfiguration
     */
    public function deserialize($data, BookReferenceConfiguration $bookReferenceConfiguration = null, array $options = [])
    {
        if (empty($bookReferenceConfiguration)) {
            $bookReferenceConfiguration = new BookReferenceConfiguration();
        }

        $this->sipe('apiKey', 'setApiKey', $data, $bookReferenceConfiguration);

        return $bookReferenceConfiguration;
    }
}
