<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum_category")
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ForumBundle\Entity\Forum",
     *     inversedBy="categories"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $forum;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ForumBundle\Entity\Subject",
     *     mappedBy="category"
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $subjects;

    /**
     * @ORM\Column(name="created", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $modificationDate;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $name;

    public function __construct()
    {
        $this->subjects = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setForum(Forum $forum)
    {
        $this->forum = $forum;
    }

    public function getForum()
    {
        return $this->forum;
    }

    public function addSubject(Subject $subject)
    {
        $this->subjects->add($subject);
    }

    public function getSubjects()
    {
        return $this->subjects;
    }

    public function removeSubject(Subject $subject)
    {
        $this->subjects->remove($subject);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setCreationDate($date)
    {
        $this->creationDate = $date;
    }

    public function setModificationDate($date)
    {
        $this->modificationDate = $date;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function getModificationDate()
    {
        return $this->modificationDate;
    }
}
