<?php

namespace Icap\BibliographyBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * Book Reference.
 *
 * @ORM\Table(name="icap__bibliography_book_reference")
 * @ORM\Entity()
 */
class BookReference extends AbstractResource
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $author;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $abstract;

    /**
     * @ORM\Column(type="string", nullable=true, length=14)
     */
    protected $isbn;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     */
    protected $publisher;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     */
    protected $printer;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $publicationYear;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     */
    protected $language;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pageCount;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $coverUrl;

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * @param string $abstract
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * @return string
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * @param string $isbn
     */
    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;
    }

    /**
     * @return string
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * @param string $publisher
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @return string
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * @param string $printer
     */
    public function setPrinter($printer)
    {
        $this->printer = $printer;
    }

    /**
     * @return int
     */
    public function getPublicationYear()
    {
        return $this->publicationYear;
    }

    /**
     * @param int $publicationYear
     */
    public function setPublicationYear($publicationYear)
    {
        $this->publicationYear = $publicationYear;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        return $this->pageCount;
    }

    /**
     * @param int $pageCount
     */
    public function setPageCount($pageCount)
    {
        $this->pageCount = $pageCount;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getCoverUrl()
    {
        return $this->coverUrl;
    }

    /**
     * @param string $coverUrl
     */
    public function setCoverUrl($coverUrl)
    {
        $this->coverUrl = $coverUrl;
    }
}
