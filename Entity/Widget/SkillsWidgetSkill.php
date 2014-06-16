<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="icap__portfolio_widget_skills_skill")
 * @ORM\Entity
 */
class SkillsWidgetSkill
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\PortfolioBundle\Entity\Widget\SkillsWidget", inversedBy="skills")
     * @ORM\JoinColumn(name="skills_widget_id", referencedColumnName="id", nullable=false)
     */
    private $skillsWidget;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return SkillsWidgetSkill
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $skillsWidget
     *
     * @return SkillsWidgetSkill
     */
    public function setSkillsWidget($skillsWidget)
    {
        $this->skillsWidget = $skillsWidget;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSkillsWidget()
    {
        return $this->skillsWidget;
    }
}
 