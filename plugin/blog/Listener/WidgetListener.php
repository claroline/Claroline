<?php

namespace Icap\BlogBundle\Listener;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Icap\BlogBundle\Entity\BlogOptions;
use Icap\BlogBundle\Entity\WidgetBlog;
use Icap\BlogBundle\Entity\WidgetBlogList;
use Icap\BlogBundle\Entity\WidgetTagListBlog;
use Icap\BlogBundle\Form\WidgetBlogType;
use Icap\BlogBundle\Form\WidgetListType;
use Icap\BlogBundle\Form\WidgetTagListBlogType;
use Icap\BlogBundle\Manager\TagManager;
use Icap\BlogBundle\Manager\WidgetManager;
use Icap\BlogBundle\Repository\PostRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service
 */
class WidgetListener
{
    /** @var WidgetManager */
    private $widgetManager;

    /** @var FormFactoryInterface  */
    private $formFactory;

    /** @var EngineInterface  */
    private $templatingEngine;

    /** @var WidgetListType */
    private $widgetListType;

    /** @var WidgetBlogType */
    private $widgetBlogType;

    /** @var PostRepository */
    private $postRepository;

    /** @var TagManager */
    private $tagManager;

    /** @var WidgetTagListBlogType  */
    private $widgetTagListBlogType;

    /**
     * @DI\InjectParams({
     *      "widgetManager" = @DI\Inject("icap_blog.manager.widget"),
     *      "formFactory" = @DI\Inject("form.factory"),
     *      "templatingEngine" = @DI\Inject("templating"),
     *      "widgetListType" = @DI\Inject("icap_blog.form.widget_list"),
     *      "widgetBlogType" = @DI\Inject("icap_blog.form.widget_blog"),
     *      "postRepository" = @DI\Inject("icap.blog.post_repository"),
     *      "tagManager" = @DI\Inject("icap.blog.manager.tag"),
     *      "widgetTagListBlogType" = @DI\Inject("icap_blog.form.widget_tag_list_blog")
     * })
     */
    public function __construct(WidgetManager $widgetManager, FormFactoryInterface $formFactory,
        EngineInterface $templatingEngine, WidgetListType $widgetListType, WidgetBlogType $widgetBlogType,
        PostRepository $postRepository, TagManager $tagManager, WidgetTagListBlogType $widgetTagListBlogType)
    {
        $this->widgetManager = $widgetManager;
        $this->formFactory = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->widgetListType = $widgetListType;
        $this->widgetBlogType = $widgetBlogType;
        $this->postRepository = $postRepository;
        $this->tagManager = $tagManager;
        $this->widgetTagListBlogType = $widgetTagListBlogType;
    }

    /**
     * @DI\Observe("widget_blog_list")
     */
    public function onWidgetListDisplay(DisplayWidgetEvent $event)
    {
        $blogs = $this->widgetManager->getBlogs($event->getInstance());
        $widgetOptions = $this->widgetManager->getWidgetListOptions($event->getInstance());

        $content = $this->templatingEngine->render(
            'IcapBlogBundle:widget:list.html.twig',
            array(
                'blogs' => $blogs,
                'diplayStyle' => $widgetOptions->getDisplayStyle(),
            )
        );

        $event->setContent($content);

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_blog_list_configuration")
     */
    public function onWidgetListConfigure(ConfigureWidgetEvent $event)
    {
        $widgetBlogList = new WidgetBlogList();
        $widgetBlogList->setWidgetListBlogs($this->widgetManager->getWidgetListBlogs($event->getInstance()));
        $widgetBlogList->setWidgetDisplayListBlogs($this->widgetManager->getWidgetListOptions($event->getInstance())->getDisplayStyle());

        $form = $this->formFactory->create($this->widgetListType, $widgetBlogList);

        $content = $this->templatingEngine->render(
            'IcapBlogBundle:widget:listConfigure.html.twig',
            array(
                'form' => $form->createView(),
                'widgetInstance' => $event->getInstance(),
            )
        );

        $event->setContent($content);
    }

    /**
     * @DI\Observe("widget_blog")
     */
    public function onWidgetBlogDisplay(DisplayWidgetEvent $event)
    {
        $blog = $this->widgetManager->getBlog($event->getInstance());

        $posts = [];

        if (null !== $blog) {
            $query = $this->postRepository
                ->createQueryBuilder('post')
                ->andWhere('post.blog = :blogId')
                ->setParameter('blogId', $blog->getId())
                ->orderBy('post.publicationDate', 'DESC')
            ;

            $posts = $this->postRepository->filterByPublishPost($query)->getQuery()->getResult();
        }

        $content = $this->templatingEngine->render(
            'IcapBlogBundle:widget:blog.html.twig',
            [
                'blog' => $blog,
                'posts' => $posts,
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_blog_configuration")
     */
    public function onWidgetBlogConfigure(ConfigureWidgetEvent $event)
    {
        $widgetBlog = new WidgetBlog();
        $widgetBlog->setResourceNode($this->widgetManager->getResourceNodeOfWidgetBlog($event->getInstance()));

        $form = $this->formFactory->create($this->widgetBlogType, $widgetBlog);

        $content = $this->templatingEngine->render(
            'IcapBlogBundle:widget:blogConfigure.html.twig',
            array(
                'form' => $form->createView(),
                'widgetInstance' => $event->getInstance(),
            )
        );

        $event->setContent($content);
    }

    /**
     * @DI\Observe("widget_tag_list")
     */
    public function onWidgetTagListDisplay(DisplayWidgetEvent $event)
    {
        $blog = $this->widgetManager->getTagListBlog($event->getInstance());
        /** @var \icap\BlogBundle\Entity\WidgetTagListBlog $widgetTagListBlog */
        $widgetTagListBlog = $this->widgetManager->getWidgetTagListBlogByWdgetInstance($event->getInstance());

        $tagCloud = 0;

        if ($widgetTagListBlog !== null) {
            $tagCloud = $widgetTagListBlog->getTagCloud();
        }

        $blogOptions = new BlogOptions();
        $blogOptions->setTagCloud($tagCloud);

        $content = $this->templatingEngine->render(
            'IcapBlogBundle:widget:tags.html.twig',
            [
                'blog' => $blog,
                '_resource' => $blog,
                'blogOptions' => $blogOptions,
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_tag_list_configuration")
     */
    public function onWidgetTagListConfiguration(ConfigureWidgetEvent $event)
    {
        /** @var \icap\BlogBundle\Entity\WidgetTagListBlog $widgetTagListBlog */
        $widgetTagListBlog = $this->widgetManager->getWidgetTagListBlogByWdgetInstance($event->getInstance());

        if (null === $widgetTagListBlog) {
            $widgetTagListBlog = new WidgetTagListBlog();
            $widgetTagListBlog->setResourceNode($this->widgetManager->getResourceNodeOfWidgetTagListBlog($event->getInstance()));
        }

        $form = $this->formFactory->create($this->widgetTagListBlogType, $widgetTagListBlog);

        $content = $this->templatingEngine->render(
            'IcapBlogBundle:widget:tagListBlogConfigure.html.twig',
            array(
                'form' => $form->createView(),
                'widgetInstance' => $event->getInstance(),
            )
        );

        $event->setContent($content);
    }
}
