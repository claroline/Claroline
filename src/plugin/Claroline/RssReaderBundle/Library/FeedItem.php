<?php

namespace Claroline\RssReaderBundle\Library;

/**
 * Simple data class storing common feed items attributes.
 *
 * @todo Add support for enclosures
 */
class FeedItem
{
    private $title;
    private $description;
    private $link;
    private $author;
    private $date;
    private $guid;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = (string) $title ?: null;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = (string) $description ?: null;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setLink($link)
    {
        $this->link = (string) $link ?: null;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = (string) $author ?: null;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = (string) $date ?: null;
    }

    public function getGuid()
    {
        return $this->guid;
    }

    public function setGuid($guid)
    {
        $this->guid = (string) $guid ?: null;
    }
}