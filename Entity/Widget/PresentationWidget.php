<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="icap__portfolio_widget_presentation")
 * @ORM\Entity
 */
class PresentationWidget extends AbstractWidget
{
    protected $widgetType = 'presentation';

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $presentation;

    /**
     * @param string $description
     *
     * @return UserInformationWidget
     */
    public function setPresentation($description)
    {
        $this->presentation = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getPresentation()
    {
        return $this->presentation;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return array(
            'presentation' => $this->getPresentation()
        );
    }

    /**
     * @return array
     */
    public function getEmpty()
    {
        return array(
            'presentation' => null
        );
    }
}
