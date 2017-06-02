<?php

namespace Icap\BlogBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\BlogOptions;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Entity\Tag;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @DI\Service("icap_blog.manager.blog")
 */
class BlogManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @DI\InjectParams({
     *      "objectManager" = @DI\Inject("claroline.persistence.object_manager"),
     *      "uploadDir" = @DI\Inject("%icap.blog.banner_directory%")
     * })
     */
    public function __construct(ObjectManager $objectManager, $uploadDir)
    {
        $this->objectManager = $objectManager;
        $this->uploadDir = $uploadDir;
    }

    /**
     * @param Workspace $workspace
     * @param array     $files
     * @param Blog      $object
     *
     * @return array
     */
    public function exportBlog(Workspace $workspace, array &$files, Blog $object)
    {
        $data = [];

        $infosUid = uniqid().'.txt';
        $infosTemporaryPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$infosUid;
        file_put_contents($infosTemporaryPath, $object->getInfos());
        $files[$infosUid] = $infosTemporaryPath;

        $data['infos_path'] = $infosUid;
        $data['options'] = [
            'authorize_comment' => $object->getOptions()->getAuthorizeComment(),
            'authorize_anonymous_comment' => $object->getOptions()->getAuthorizeAnonymousComment(),
            'post_per_page' => $object->getOptions()->getPostPerPage(),
            'auto_publish_post' => $object->getOptions()->getAutoPublishPost(),
            'auto_publish_comment' => $object->getOptions()->getAutoPublishComment(),
            'display_title' => $object->getOptions()->getDisplayTitle(),
            'banner_activate' => $object->getOptions()->isBannerActivate(),
            'display_post_view_counter' => $object->getOptions()->getDisplayPostViewCounter(),
            'banner_background_color' => $object->getOptions()->getBannerBackgroundColor(),
            'banner_height' => $object->getOptions()->getBannerHeight(),
            'banner_background_image' => $object->getOptions()->getBannerBackgroundImage(),
            'banner_background_image_position' => $object->getOptions()->getBannerBackgroundImagePosition(),
            'banner_background_image_repeat' => $object->getOptions()->getBannerBackgroundImageRepeat(),
            'tag_cloud' => (null === $object->getOptions()->getTagCloud()) ? 0 : $object->getOptions()->getTagCloud(),
        ];

        $data['posts'] = [];

        foreach ($object->getPosts() as $post) {
            $postUid = uniqid().'.txt';
            $postTemporaryPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$postUid;
            file_put_contents($postTemporaryPath, $post->getContent());
            $files[$postUid] = $postTemporaryPath;

            $tags = [];

            foreach ($post->getTags() as $tag) {
                $tags[] = [
                    'name' => $tag->getName(),
                ];
            }

            $comments = [];

            foreach ($post->getComments() as $comment) {
                $commentUid = uniqid().'.txt';
                $commentTemporaryPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$commentUid;
                file_put_contents($commentTemporaryPath, $comment->getMessage());
                $files[$commentUid] = $commentTemporaryPath;

                $comments[] = [
                    'message' => $commentUid,
                    'author' => $comment->getAuthor()->getMail(),
                    'creation_date' => $comment->getCreationDate()->format('Y-m-d H:i:s'),
                    'update_date' => (null !== $comment->getUpdateDate()) ? $comment->getUpdateDate()->format('Y-m-d H:i:s') : null,
                    'publication_date' => (null !== $comment->getPublicationDate()) ? $comment->getPublicationDate()->format('Y-m-d H:i:s') : null,
                    'status' => $comment->getStatus(),
                ];
            }

            $postArray = [
                'title' => $post->getTitle(),
                'content' => $postUid,
                'author' => $post->getAuthor()->getMail(),
                'status' => $post->getStatus(),
                'creation_date' => $post->getCreationDate()->format('Y-m-d H:i:s'),
                'modification_date' => (null !== $post->getModificationDate()) ? $post->getModificationDate()->format('Y-m-d H:i:s') : null,
                'publication_date' => (null !== $post->getPublicationDate()) ? $post->getPublicationDate()->format('Y-m-d H:i:s') : null,
                'tags' => $tags,
                'comments' => $comments,
            ];

            $data['posts'][] = $postArray;
        }

        return $data;
    }

    public function createUploadFolder($uploadFolderPath)
    {
        if (!file_exists($uploadFolderPath)) {
            mkdir($uploadFolderPath, 0777, true);
        }
    }

    /**
     * @param array  $data
     * @param string $rootPath
     * @param User   $owner
     *
     * @return Blog
     */
    public function importBlog(array $data, $rootPath, User $owner)
    {
        $blogDatas = $data['data'];
        $optionsData = $blogDatas['options'];

        $blogOptions = new BlogOptions();
        $blogOptions
            ->setAuthorizeComment($optionsData['authorize_comment'])
            ->setAuthorizeAnonymousComment($optionsData['authorize_anonymous_comment'])
            ->setPostPerPage($optionsData['post_per_page'])
            ->setAutoPublishPost($optionsData['auto_publish_post'])
            ->setAutoPublishComment($optionsData['auto_publish_comment'])
            ->setDisplayTitle($optionsData['display_title'])
            ->setBannerActivate($optionsData['banner_activate'])
            ->setDisplayPostViewCounter($optionsData['display_post_view_counter'])
            ->setBannerBackgroundColor($optionsData['banner_background_color'])
            ->setBannerHeight($optionsData['banner_height'])
            ->setBannerBackgroundImagePosition($optionsData['banner_background_image_position'])
            ->setBannerBackgroundImageRepeat($optionsData['banner_background_image_repeat'])
            ->setTagCloud($optionsData['tag_cloud']);

        $blog = new Blog();
        if (isset($blogDatas['infos_path']) && $blogDatas['infos_path'] !== null) {
            $infos = file_get_contents(
                $rootPath.DIRECTORY_SEPARATOR.$blogDatas['infos_path']
            );
            $blog->setInfos($infos);
        }
        $blog->setOptions($blogOptions);
        $this->objectManager->persist($blog);
        //flush, otherwise we dont have the website ID needed for building uploadPath for banner
        $this->objectManager->forceFlush();

        //Copy banner bg image to web folder
        if (isset($optionsData['banner_background_image']) && $optionsData['banner_background_image'] !== null && !filter_var($optionsData['banner_background_image'], FILTER_VALIDATE_URL)) {
            $this->createUploadFolder(DIRECTORY_SEPARATOR.$this->uploadDir);
            $uniqid = uniqid();
            copy(
                $rootPath.DIRECTORY_SEPARATOR.$optionsData['banner_background_image'],
                DIRECTORY_SEPARATOR.$this->uploadDir.DIRECTORY_SEPARATOR.$uniqid
            );
            $blogOptions->setBannerBackgroundImage($uniqid);
        }

        $postsDatas = $blogDatas['posts'];
        $posts = new ArrayCollection();

        foreach ($postsDatas as $postsData) {
            $post = new Post();

            $tagsDatas = $postsData['tags'];
            $tags = new ArrayCollection();

            foreach ($tagsDatas as $tagsData) {
                $tag = $this->retrieveTag($tagsData['name']);
                $tags->add($tag);
            }

            $commentsDatas = $postsData['comments'];
            $comments = new ArrayCollection();
            foreach ($commentsDatas as $commentsData) {
                $comment = new Comment();
                $commentMessage = file_get_contents($rootPath.DIRECTORY_SEPARATOR.$commentsData['message']);
                $comment
                    ->setMessage($commentMessage)
                    ->setAuthor($this->retrieveUser($commentsData['author'], $owner))
                    ->setCreationDate(new \DateTime($postsData['creation_date']))
                    ->setUpdateDate(new \DateTime($postsData['modification_date']))
                    ->setPublicationDate(new \DateTime($postsData['publication_date']))
                    ->setStatus($commentsData['status'])
                ;
                $this->objectManager->persist($comment);
                $comments->add($comment);
            }

            $postContent = file_get_contents($rootPath.DIRECTORY_SEPARATOR.$postsData['content']);

            $post
                ->setTitle($postsData['title'])
                ->setContent($postContent)
                ->setAuthor($this->retrieveUser($postsData['author'], $owner))
                ->setCreationDate(new \DateTime($postsData['creation_date']))
                ->setModificationDate(new \DateTime($postsData['modification_date']))
                ->setPublicationDate(new \DateTime($postsData['publication_date']))
                ->setTags($tags)
                ->setComments($comments)
                ->setStatus($postsData['status'])
            ;

            $posts->add($post);
        }

        $blog->setPosts($posts);

        return $blog;
    }

    /**
     * @param string $mail
     * @param User   $owner
     *
     * @return User|null
     */
    protected function retrieveUser($mail, User $owner)
    {
        $user = $this->objectManager->getRepository('ClarolineCoreBundle:User')->findOneByMail($mail);

        if (null === $user) {
            $user = $owner;
        }

        return $user;
    }

    /**
     * This method is used by the workspace import function.
     *
     * @param string $name
     *
     * @return Tag
     */
    protected function retrieveTag($name)
    {
        $tag = $this->objectManager->getRepository('IcapBlogBundle:Tag')->findOneByName($name);

        if (!$tag) {
            //let's look if it's scheduled for an Insert...
            $tag = $this->getTagFromIdentityMapOrScheduledForInsert($name);

            if (!$tag) {
                $tag = new Tag();
                $tag->setName($name);
                $this->objectManager->persist($tag);
            }
        }

        return $tag;
    }

    private function getTagFromIdentityMapOrScheduledForInsert($name)
    {
        $res = $this->getTagFromIdentityMap($name);

        if ($res) {
            return $res;
        }

        return $this->getTagScheduledForInsert($name);
    }

    private function getTagScheduledForInsert($name)
    {
        $scheduledForInsert = $this->objectManager->getUnitOfWork()->getScheduledEntityInsertions();

        foreach ($scheduledForInsert as $entity) {
            if (get_class($entity) === 'Icap\BlogBundle\Entity\Tag') {
                if (strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $entity->getName())) === strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name))) {
                    return $entity;
                }
            }
        }

        return;
    }

    private function getTagFromIdentityMap($name)
    {
        $map = $this->objectManager->getUnitOfWork()->getIdentityMap();

        if (!array_key_exists('Icap\BlogBundle\Entity\Tag', $map)) {
            return;
        }

        //so it was in the identityMap hey !
        foreach ($map['Icap\BlogBundle\Entity\Tag'] as $tag) {
            if (strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $tag->getName())) === strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name))) {
                return $tag;
            }
        }

        return;
    }

    /**
     * @param UploadedFile $file
     * @param BlogOptions  $options
     */
    public function updateBanner(UploadedFile $file = null, BlogOptions $options)
    {
        $ds = DIRECTORY_SEPARATOR;

        if (file_exists($this->uploadDir.$ds.$options->getBannerBackgroundImage()) || $file === null) {
            @unlink($this->uploadDir.$ds.$options->getBannerBackgroundImage());
        }

        if ($file) {
            $uniqid = uniqid();
            $options->setBannerBackgroundImage($uniqid);
            $file->move($this->uploadDir, $uniqid);
        } else {
            $options->setBannerBackgroundImage(null);
        }

        $this->objectManager->persist($options);
        $this->objectManager->flush();
    }

    public function getPanelInfos()
    {
        return [
            'search',
            'infobar',
            'rss',
            'tagcloud',
            'redactor',
            'calendar',
            'archives',
        ];
    }

    public function updateOptions(Blog $blog, BlogOptions $options)
    {
        // Remove old options and flush before adding the new ones
        $oldOptions = $blog->getOptions();
        $this->objectManager->remove($oldOptions);
        $this->objectManager->flush();

        // Define the new options
        $blog->setOptions($options);
        $this->objectManager->persist($options);
        $this->objectManager->persist($blog);
        $this->objectManager->flush();

        return $this->objectManager->getUnitOfWork();
    }
}
