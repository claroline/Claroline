<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gaetan
 * Date: 26/06/13
 * Time: 15:52
 * To change this template use File | Settings | File Templates.
 */

namespace Icap\LessonBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__lesson")
 * @ORM\HasLifecycleCallbacks()
 * @ExclusionPolicy("all")
 */
class Lesson extends AbstractResource
{
    /**
     * @ORM\OneToOne(targetEntity="Icap\LessonBundle\Entity\Chapter", cascade={"all"})
     * @ORM\JoinColumn(name="root_id", referencedColumnName="id", onDelete="CASCADE")
     * @Expose
     */
    private $root;

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
        if ($this->getRoot() == null) {
            $em = $event->getEntityManager();
            $rootLesson = $this->getRoot();
            if ($rootLesson == null) {
                $rootLesson = new Chapter();
                $rootLesson->setLesson($this);
                $rootLesson->setTitle('root_'.$this->getId());
                $this->setRoot($rootLesson);

                $em->getRepository('IcapLessonBundle:Chapter')->persistAsFirstChild($rootLesson);
                $em->flush();
            }
        }
    }
}
