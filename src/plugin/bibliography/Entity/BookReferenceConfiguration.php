<?php

namespace Icap\BibliographyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BookReferenceConfiguration Entity.
 *
 * @ORM\Table(name="icap__bibliography_book_reference_configuration")
 * @ORM\Entity(repositoryClass="Icap\BibliographyBundle\Repository\BookReferenceConfigurationRepository")
 */
class BookReferenceConfiguration
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="api_key", type="string", nullable=true)
     */
    protected $apiKey;

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}
