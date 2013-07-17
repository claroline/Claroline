<?php

namespace ICAP\BlogBundle\Listener;

use Claroline\CoreBundle\Event\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Event\OpenResourceEvent;
use Doctrine\Common\Collections\ArrayCollection;
use ICAP\BlogBundle\Entity\Blog;
use ICAP\BlogBundle\Entity\Comment;
use ICAP\BlogBundle\Entity\Post;
use ICAP\BlogBundle\Form\BlogType;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BlogListener extends ContainerAware
{
    /**
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new BlogType(), new Blog());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'icap_blog'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new BlogType(), new Blog());
        $form->bind($request);

        if ($form->isValid()) {
            $event->setResources(array($form->getData()));
            $event->stopPropagation();

            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'icap_blog'
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $route = $this->container
            ->get('router')
            ->generate(
                'icap_blog_view',
                array('blogId' => $event->getResource()->getId())
            );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /**
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event)
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        /** @var \ICAP\BlogBundle\Entity\Blog $blog */
        $blog      = $event->getResource();

        $newBlog = new Blog();
        $newBlog->setName($blog->getName());

        $newPosts = new ArrayCollection();

        foreach ($blog->getPosts() as $post) {
            /** @var \ICAp\BlogBundle\Entity\Post $newPost */
            $newPost = new Post();
            $newPost
                ->setTitle($post->getTitle())
                ->setContent($post->getContent())
                ->setAuthor($post->getAuthor())
            ;

            $newPosts->add($newPost);

            $newComments = new ArrayCollection();

            foreach ($post->getComments() as $comment) {
                /** @var \ICAp\BlogBundle\Entity\Comment $newComment */
                $newComment = new Comment();
                $newComment
                    ->setAuthor($comment->getAuthor())
                    ->setMessage($comment->getMessage())
                ;

                $newComments->add($newComment);
            }

            $newPost->setComments($newComments);
        }

        $newBlog->setPosts($newPosts);

        $entityManager->persist($newBlog);

        $event->setCopy($newBlog);
        $event->stopPropagation();
    }
}
