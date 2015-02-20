<?php

namespace Icap\BlogBundle\Listener;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Icap\BlogBundle\Entity\WidgetBlog;
use Icap\BlogBundle\Entity\WidgetBlogList;
use Icap\BlogBundle\Form\WidgetBlogType;
use Icap\BlogBundle\Form\WidgetListType;
use Icap\BlogBundle\Manager\WidgetManager;
use Icap\BlogBundle\Repository\PostRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
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


    /**
     * @DI\InjectParams({
     *      "widgetManager"    = @DI\Inject("icap_blog.manager.widget"),
     *      "formFactory"      = @DI\Inject("form.factory"),
     *      "templatingEngine" = @DI\Inject("templating"),
     *      "widgetListType"   = @DI\Inject("icap_blog.form.widget_list"),
     *      "widgetBlogType"   = @DI\Inject("icap_blog.form.widget_blog"),
     *      "postRepository"   = @DI\Inject("icap.blog.post_repository")
     * })
     */
    public function __construct(WidgetManager $widgetManager, FormFactoryInterface $formFactory,
        EngineInterface $templatingEngine, WidgetListType $widgetListType, WidgetBlogType $widgetBlogType, PostRepository $postRepository)
    {
        $this->widgetManager    = $widgetManager;
        $this->formFactory      = $formFactory;
        $this->templatingEngine = $templatingEngine;
        $this->widgetListType   = $widgetListType;
        $this->widgetBlogType   = $widgetBlogType;
        $this->postRepository   = $postRepository;
    }

     /**
     * @DI\Observe("widget_blog_list")
     */
    public function onWidgetListDisplay(DisplayWidgetEvent $event)
    {
        $blogs = $this->widgetManager->getBlogs($event->getInstance());

        $content = $this->templatingEngine->render(
            'IcapBlogBundle:widget:list.html.twig',
            array('blogs' => $blogs)
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

        $form = $this->formFactory->create($this->widgetListType, $widgetBlogList);

        $content = $this->templatingEngine->render(
            'IcapBlogBundle:widget:listConfigure.html.twig',
            array(
                'form'           => $form->createView(),
                'widgetInstance' => $event->getInstance()
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
                'blog'  => $blog,
                'posts' => $posts
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
        $widgetBlog->setResourceNode($this->widgetManager->getResourceNode($event->getInstance()));

        $form = $this->formFactory->create($this->widgetBlogType, $widgetBlog);

        $content = $this->templatingEngine->render(
            'IcapBlogBundle:widget:blogConfigure.html.twig',
            array(
                'form'           => $form->createView(),
                'widgetInstance' => $event->getInstance()
            )
        );

        $event->setContent($content);
    }
}
