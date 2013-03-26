<?php

namespace Claroline\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum")
 * @ORM\Entity(repositoryClass="Claroline\ForumBundle\Repository\ForumRepository")
 */
class Forum extends AbstractResource
{

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ForumBundle\Entity\Subject",
     *     mappedBy="forum"
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $subjects;

    public function __construct()
    {
        $this->subjects = new ArrayCollection();
    }

    public function getSubjects()
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject)
    {
        $this->subjects->add($subject);
    }

    public function removeSubject(Subject $subject)
    {
        $this->subjects->removeElement($subject);
    }
}