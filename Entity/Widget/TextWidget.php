<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="icap__portfolio_widget_text")
 * @ORM\Entity
 */
class TextWidget extends AbstractWidget
{
    protected $widgetType = 'text';

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;

    /**
     * @param string $text
     *
     * @return UserInformationWidget
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return array(
            'text' => $this->getText()
        );
    }

    /**
     * @return array
     */
    public function getEmpty()
    {
        return array(
            'text' => null
        );
    }
}
