<?php

namespace Icap\PortfolioBundle\Entity\Widget;

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
     * @var SkillsWidgetSkill[]
     *
     * @ORM\OneToMany(targetEntity="Icap\PortfolioBundle\Entity\Widget\SkillsWidgetSkill", mappedBy="skillsWidget", cascade={"persist", "remove"})
     */
    protected $skills;

    /**
     * @param \Icap\PortfolioBundle\Entity\Widget\SkillsWidgetSkill[] $skills
     *
     * @return SkillsWidget
     */
    public function setSkills($skills)
    {
        foreach ($skills as $skill) {
            $skill->setSkillsWidget($this);
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
        $skills = $this->getSkills();
        $data = array(
            'id'     => $this->getId(),
            'skills' => array()
        );

        foreach ($skills as $skill) {
            $data['skills'][] = array(
                'name' => $skill->getName()
            );
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getEmpty()
    {
        return array(
            'skills' => array()
        );
    }
}
