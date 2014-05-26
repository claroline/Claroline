<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="icap__portfolio_widget_title",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="portfolio_slug_unique_idx", columns={"slug"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="Icap\PortfolioBundle\Repository\Widget\TitleWidgetRepository")
 */
class TitleWidget extends AbstractWidget
{
    protected $widgetType = 'title';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=false)
     * @Assert\Length(max = "128")
     */
    protected $title;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     * @ORM\Column(type="string", length=128, unique=true, nullable=false)
     */
    protected $slug;

    /**
     * @param string|null $slug
     *
     * @return TitleWidget
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $title
     *
     * @return TitleWidget
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return array(
            'value' => $this->getTitle()
        );
    }
}
