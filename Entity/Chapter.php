<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gaetan
 * Date: 26/06/13
 * Time: 15:52
 * To change this template use File | Settings | File Templates.
 */
namespace ICAP\LessonBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__lesson_chapter")
 */
class Chapter
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;
    /**
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="ICAP\LessonBundle\Entity\Lesson")
     * @ORM\JoinColumn(name="lesson_id", referencedColumnName="id")
     */
    private $lesson;

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $lesson
     */
    public function setLesson($lesson)
    {
        $this->lesson = $lesson;
    }

    /**
     * @return mixed
     */
    public function getLesson()
    {
        return $this->lesson;
    }


}