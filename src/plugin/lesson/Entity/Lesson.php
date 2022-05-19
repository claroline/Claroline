<?php

namespace Icap\LessonBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__lesson")
 * @ORM\HasLifecycleCallbacks()
 */
class Lesson extends AbstractResource
{
    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @var string
     */
    private $description;

    /**
     * Show overview to users or directly start the lesson.
     *
     * @ORM\Column(name="show_overview", type="boolean", options={"default" = 1})
     *
     * @var bool
     */
    private $showOverview = true;

    /**
     * @ORM\OneToOne(targetEntity="Icap\LessonBundle\Entity\Chapter", cascade={"all"})
     * @ORM\JoinColumn(name="root_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription(string $description = null)
    {
        $this->description = $description;
    }

    /**
     * Set show overview.
     *
     * @param bool $showOverview
     */
    public function setShowOverview($showOverview)
    {
        $this->showOverview = $showOverview;
    }

    /**
     * Is overview shown ?
     *
     * @return bool
     */
    public function getShowOverview()
    {
        return $this->showOverview;
    }

    /**
     * @param mixed $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @ORM\PostPersist
     */
    public function createRoot(LifecycleEventArgs $event)
    {
        $em = $event->getEntityManager();
        $rootLesson = $this->buildRoot();

        $em->getRepository(Chapter::class)->persistAsFirstChild($rootLesson);
        $em->flush();
    }

    public function buildRoot()
    {
        $rootLesson = $this->getRoot();

        if (!$rootLesson) {
            $rootLesson = new Chapter();
            $rootLesson->setLesson($this);
            $rootLesson->setTitle('root_'.$this->getId());
            $this->setRoot($rootLesson);
        }

        return $rootLesson;
    }
}
