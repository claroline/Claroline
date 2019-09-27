<?php

namespace Icap\BlogBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Icap\BlogBundle\Entity\Post;
use Symfony\Component\HttpFoundation\File\File;

class Updater120000 extends Updater
{
    private $container;

    /** @var Connection */
    private $conn;

    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        //migrate blog banners to core
        $this->migrateBanners();
        //migrate blog tags to tabBundle tags
        $this->migrateTags();
        //set new CommentModerationMode attribute value
        $this->setCommentModerationMode();
        //deletes old post action
        $this->deleteOldPostAction();
        //TODO drop blog tags tables for a future version
    }

    private function migrateBanners()
    {
        $this->log('Transfer blog banners to resourceNode');
        $om = $this->container->get('claroline.persistence.object_manager');
        $repo = $om->getRepository('Icap\BlogBundle\Entity\Blog');
        $fu = $this->container->get('claroline.utilities.file');
        $uploadDir = $this->container->getParameter('icap.blog.banner_directory');

        $batchSize = 200;
        $i = 0;
        $om->startFlushSuite();
        $page = 1;
        $blogs = [];
        while (1 === $page || count($blogs) >= $batchSize) {
            //get batch
            $blogs = $repo->findBy([], ['id' => 'ASC'], $batchSize, $batchSize * ($page - 1));
            foreach ($blogs as $blog) {
                if ($blog->getOptions() && !empty($blog->getOptions()->getBannerBackgroundImage()) && empty($blog->getResourceNode()->getPoster())) {
                    $bannerPath = $uploadDir.DIRECTORY_SEPARATOR.$blog->getOptions()->getBannerBackgroundImage();
                    if (file_exists($bannerPath)) {
                        $publicFile = $fu->createFile(new File($bannerPath));
                        $blog->getResourceNode()->setPoster($publicFile->getUrl());
                        ++$i;
                        if (0 === $i % 50) {
                            $om->forceFlush();
                        }
                    }
                }
            }
            ++$page;
        }

        $om->endFlushSuite();
    }

    private function migrateTags()
    {
        //check if table still exists beforehand
        if ($this->conn->getSchemaManager()->tablesExist(['icap__blog_post_tag'])
            && $this->conn->getSchemaManager()->tablesExist(['icap__blog_tag'])) {
            $this->log('Transfer blog tags to tagBundle');
            $om = $this->container->get('claroline.persistence.object_manager');
            $serializer = $this->container->get('Icap\BlogBundle\Serializer\PostSerializer');
            $repo = $om->getRepository('Icap\BlogBundle\Entity\Post');

            $batchSize = 500;
            $i = 0;
            $om->startFlushSuite();
            $page = 1;
            $posts = [];
            while (1 === $page || count($posts) >= $batchSize) {
                //get batch
                $posts = $repo->findBy([], ['id' => 'ASC'], $batchSize, $batchSize * ($page - 1));
                foreach ($posts as $post) {
                    //DEPRECATED only use for migrations
                    $tags = $post->getTags();
                    if (!empty($tags)) {
                        $serializer->deserializeTags($post, implode(',', $tags->toArray()));
                        ++$i;
                        if (0 === $i % 100) {
                            $om->forceFlush();
                        }
                    }
                }
                ++$page;
            }

            $om->endFlushSuite();
        }
    }

    private function setCommentModerationMode()
    {
        $this->log('Updating new commentModerationMode attribute from old autoPublishComment');
        $om = $this->container->get('claroline.persistence.object_manager');
        $repo = $om->getRepository('Icap\BlogBundle\Entity\BlogOptions');
        $options = $repo->findAll();
        $i = 0;

        $om->startFlushSuite();

        foreach ($options as $options) {
            if (is_null($options->getCommentModerationMode())) {
                $options->setCommentModerationMode($options->getAutoPublishComment() ? 0 : 2);
                ++$i;
                if (0 === $i % 200) {
                    $om->forceFlush();
                }
            }
        }

        $om->endFlushSuite();
    }

    private function deleteOldPostAction()
    {
        $this->log('Deleting old Post action...');
        $om = $this->container->get('claroline.persistence.object_manager');
        $resourceTypeRepo = $om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $menuActionRepo = $om->getRepository('Claroline\CoreBundle\Entity\Resource\MenuAction');
        $blogType = $resourceTypeRepo->findOneBy(['name' => 'icap_blog']);

        if ($blogType) {
            $actions = $menuActionRepo->findBy(['name' => 'post', 'resourceType' => $blogType]);

            foreach ($actions as $action) {
                $om->remove($action);
            }
            $om->flush();
        }

        $this->log('Old Post action deleted.');
    }
}
