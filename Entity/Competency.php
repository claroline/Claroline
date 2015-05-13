<?php

namespace HeVinci\CompetencyBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints as BR;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="HeVinci\CompetencyBundle\Repository\CompetencyRepository")
 * @ORM\Table(name="hevinci_competency")
 */
class Competency implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="Scale", inversedBy="competencies")
     */
    private $scale;

    /**
     * @ORM\OneToMany(targetEntity="CompetencyAbility", mappedBy="competency")
     */
    private $competencyAbilities;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\Activity")
     * @ORM\JoinTable(name="hevinci_competency_activity")
     */
    private $activities;

    /**
     * @ORM\Column(type="integer")
     *
     * Note: this field denormalizes $activities data
     *       in order to decrease query complexity.
     */
    private $activityCount = 0;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Competency", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Competency", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->levels = new ArrayCollection();
        $this->activities = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param Scale $scale
     */
    public function setScale(Scale $scale)
    {
        $this->scale = $scale;
    }

    /**
     * @return Scale
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * @param Competency $parent
     */
    public function setParent(Competency $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return null|Competency
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return integer
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return integer
     */
    public function getLevel()
    {
        return $this->lvl;
    }

    /**
     * @return integer
     */
    public function getLeft()
    {
        return $this->lft;
    }

    /**
     * @return integer
     */
    public function getRight()
    {
        return $this->rgt;
    }

    /**
     * @param Activity $activity
     * @return bool
     */
    public function isLinkedToActivity(Activity $activity)
    {
        return $this->activities->contains($activity);
    }

    /**
     * Associates the ability with an activity.
     *
     * @param Activity $activity
     */
    public function linkActivity(Activity $activity)
    {
        if (!$this->isLinkedToActivity($activity)) {
            $this->activities->add($activity);
            $this->activityCount++;
        }
    }

    /**
     * Removes an association with an activity.
     *
     * @param Activity $activity
     */
    public function removeActivity(Activity $activity)
    {
        if ($this->isLinkedToActivity($activity)) {
            $this->activities->removeElement($activity);
            $this->activityCount--;
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getActivities()
    {
        return $this->activities;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'scale' => $this->scale ? $this->scale->getName() : null,
            'level' => $this->lvl,
            'activityCount' => $this->activityCount
        ];
    }
}
