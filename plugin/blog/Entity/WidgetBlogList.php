<?php

namespace Icap\BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class WidgetBlogList
{
    /**
     * @var WidgetListBlog[]
     */
    private $widgetListBlogs;
    private $widgetDisplayListBlogs = 'b';

    public function __construct()
    {
        $this->widgetListBlogs = new ArrayCollection();
    }

    /**
     * @return WidgetListBlog[]
     */
    public function getWidgetListBlogs()
    {
        return $this->widgetListBlogs;
    }

    /**
     * @param Collection|WidgetListBlog[] $widgetListBlogs
     *
     * @return WidgetBlogList
     */
    public function setWidgetListBlogs($widgetListBlogs)
    {
        $this->widgetListBlogs = $widgetListBlogs;

        return $this;
    }

    public function getWidgetDisplayListBlogs()
    {
        return $this->widgetDisplayListBlogs;
    }

    public function setWidgetDisplayListBlogs($widgetDisplayListBlogs)
    {
        $this->widgetDisplayListBlogs = $widgetDisplayListBlogs;

        return $this;
    }
}
