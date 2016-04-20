<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\RssReaderBundle\Library;

/**
 * Simple data class storing common feed information attributes.
 */
class FeedInfo
{
    private $title;
    private $imageUrl;
    private $description;
    private $link;
    private $language;
    private $author;
    private $email;
    private $lastUpdate;
    private $copyright;
    private $generator;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = (string) $title ?: null;
    }

    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = (string) $imageUrl ?: null;
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

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        $this->language = (string) $language ?: null;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = (string) $author ?: null;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = (string) $email ?: null;
    }

    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = (string) $lastUpdate ?: null;
    }

    public function getCopyright()
    {
        return $this->copyright;
    }

    public function setCopyright($copyright)
    {
        $this->copyright = (string) $copyright ?: null;
    }
    public function getGenerator()
    {
        return $this->generator;
    }

    public function setGenerator($generator)
    {
        $this->generator = (string) $generator ?: null;
    }
}
