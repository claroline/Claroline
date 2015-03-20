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
    protected $widgetType = 'experience';

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
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $startDate;

    /**
     * @var \Date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endDate;

    public function __construct()
    {
        $this->sizeX = 2;
        $this->sizeY = 2;
    }

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
     * @return \Date
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \Date $startDate
     *
     * @return ExperienceWidget
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \Date
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \Date $endDate
     *
     * @return ExperienceWidget
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = array(
            'post'        => $this->getPost(),
            'companyName' => $this->getCompanyName(),
            'startDate'   => $this->getStartDate() ? $this->getStartDate()->format('Y/m/d') : null,
            'endDate'     => $this->getEndDate() ? $this->getEndDate()->format('Y/m/d') : null
        );

        return $data;
    }

    /**
     * @return array
     */
    public function getEmpty()
    {
        return array(
            'post'        => null,
            'companyName' => null,
            'startDate'   => null,
            'endDate'     => null
        );
    }
}
