<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="icap__portfolio_widget_skills")
 * @ORM\Entity
 */
class SkillsWidget extends AbstractWidget
{
    protected $widgetType = 'skills';

    /**
     * @var SkillsWidgetSkill[]|\Doctrine\ORM\PersistentCollection
     *
     * @ORM\OneToMany(targetEntity="Icap\PortfolioBundle\Entity\Widget\SkillsWidgetSkill", mappedBy="widget", cascade={"persist", "remove"})
     */
    protected $skills;

    public function __construct()
    {
        $this->skills = new ArrayCollection();
    }

    /**
     * @param \Icap\PortfolioBundle\Entity\Widget\SkillsWidgetSkill[] $skills
     *
     * @return SkillsWidget
     */
    public function setSkills($skills)
    {
        foreach ($skills as $skill) {
            $skill->setWiidget($this);
        }

        $this->skills = $skills;

        return $this;
    }

    /**
     * @return \Icap\PortfolioBundle\Entity\Widget\SkillsWidgetSkill[]
     */
    public function getSkills()
    {
        return $this->skills;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = array(
            'id'       => $this->getId(),
            'children' => array()
        );

        foreach ($this->getSkills() as $skill) {
            $data['children'][] = $skill->getData();
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getEmpty()
    {
        return array(
            'children' => array()
        );
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->getSkills();
    }
}
