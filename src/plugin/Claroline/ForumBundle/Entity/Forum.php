<?php

namespace Claroline\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\ForumBundle\Entity\Subject;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum")
 * @ORM\Entity(repositoryClass="Claroline\ForumBundle\Repository\ForumRepository")
 */
class Forum extends AbstractResource
{
    /**
     * @ORM\OneToMany(targetEntity="Claroline\ForumBundle\Entity\Subject", mappedBy="forum", cascade={"persist"})
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

    public function addSubjects(Subject $subject)
    {
        $this->subjects->add($subject);
    }

    public function removeSubjects(Subject $subject)
    {
        $this->subjects->removeElement($subject);
    }
}