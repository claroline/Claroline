<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="icap__portfolio_widget_experience")
 * @ORM\Entity
 */
class ExperienceWidget extends AbstractWidget
{
    const WIDGET_TYPE = 'experience';
    const SIZE_X = 4;
    const SIZE_Y = 7;

    protected $widgetType = self::WIDGET_TYPE;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $post;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $companyName;

    /**
     * @var \Date
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $startDate;

    /**
     * @var \Date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $website;

    /**
     * @return string
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param string $post
     *
     * @return ExperienceWidget
     */
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     *
     * @return ExperienceWidget
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     *
     * @return ExperienceWidget
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     *
     * @return ExperienceWidget
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return ExperienceWidget
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param string $website
     *
     * @return ExperienceWidget
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = array(
            'post' => $this->getPost(),
            'companyName' => $this->getCompanyName(),
            'startDate' => $this->getStartDate() ? $this->getStartDate()->format('Y/m/d') : null,
            'endDate' => $this->getEndDate() ? $this->getEndDate()->format('Y/m/d') : null,
            'description' => $this->getDescription(),
            'website' => $this->getWebsite()
        );

        return $data;
    }

    /**
     * @return array
     */
    public function getEmpty()
    {
        return array(
            'post' => null,
            'companyName' => null,
            'startDate' => null,
            'endDate' => null,
            'description' => null,
            'website' => null
        );
    }
}
