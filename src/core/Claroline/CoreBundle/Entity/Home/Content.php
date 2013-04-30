<?php

namespace Claroline\CoreBundle\Entity\Home;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content
 *
 * @ORM\Table(name="claro_content")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ContentRepository")
 */
class Content
{
    public function __construct()
    {
        $this->created = new \Datetime();
        $this->modified = new \Datetime();
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="generated_content", type="text", nullable=true)
     */
    private $generatedContent;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="datetime")
     */
    private $modified;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Content
     */
    public function setTitle($title)
    {
        if ($title !== null) {
            $this->title = $title;
        }

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Content
     */
    public function setContent($content)
    {
        if ($content !== null) {
            $this->content = $content;
        }

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Content
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     * @return Content
     */
    public function setModified($modified = null)
    {
        if ($modified) {
            $this->modified = $modified;
        } else {
            $this->modified = new \Datetime();
        }

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set generated_content
     *
     * @param string $generatedContent
     * @return Content
     */
    public function setGeneratedContent($generatedContent)
    {
        if ($generatedContent !== null) {
            $this->generatedContent = $generatedContent;
        }

        return $this;
    }

    /**
     * Get generated_content
     *
     * @return string
     */
    public function getGeneratedContent()
    {
        return $this->generatedContent;
    }
}
