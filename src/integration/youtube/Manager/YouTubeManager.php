<?php

namespace Claroline\YouTubeBundle\Manager;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\CurlManager;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\YouTubeBundle\Entity\Video;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class YouTubeManager
{
    private CurlManager $curlManager;
    private TempFileManager $tempManager;
    private Crud $crud;
    private ObjectManager $om;
    private FileManager $fileManager;

    public function __construct(
        CurlManager $curlManager,
        TempFileManager $tempManager,
        Crud $crud,
        ObjectManager $om,
        FileManager $fileManager
    ) {
        $this->curlManager = $curlManager;
        $this->tempManager = $tempManager;
        $this->crud = $crud;
        $this->om = $om;
        $this->fileManager = $fileManager;
    }

    public function checkUrl(string $url): ?string
    {
        $videoId = $this->extractVideoId($url);

        if (empty($videoId)) {
            return 'The URL is not a correct YouTube URL.';
        }

        $headers = get_headers('https://www.youtube.com/oembed?format=json&url=http://www.youtube.com/watch?v='.$videoId);
        if (!(is_array($headers) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $headers[0]) : false)) {
            return 'This video does not exist.';
        }

        return null;
    }

    public function extractVideoId(string $url): ?string
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        if (empty($query['v'])) {
            return null;
        }

        return $query['v'];
    }

    public function handleThumbnailForVideo(Video $video): void
    {
        $resourceNode = $video->getResourceNode();
        if (!$resourceNode->getThumbnail()) {
            $uploadedFile = $this->getTemporaryThumbnailFile($video->getUrl());

            if ($uploadedFile) {
                $publicFile = $this->crud->create(PublicFile::class, [], ['file' => $uploadedFile]);

                $resourceNode->setThumbnail($publicFile->getUrl());
                $this->om->persist($resourceNode);

                $this->fileManager->linkFile(ResourceNode::class, $resourceNode->getUuid(), $publicFile->getUrl());

                $this->om->flush();
            }
        }
    }

    public function getThumbnailUrl(string $url): ?string
    {
        $videoId = $this->extractVideoId($url);

        if (empty($videoId)) {
            return null;
        }

        return "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg";
    }

    public function downloadThumbnail(string $url): ?string
    {
        $thumbnailUrl = $this->getThumbnailUrl($url);

        if (!$thumbnailUrl) {
            return null;
        }

        try {
            $thumbnailFile = $this->curlManager->exec($thumbnailUrl);
        } catch (\Exception) {
            return null;
        }

        return $thumbnailFile;
    }

    public function getTemporaryThumbnailFile(string $url): ?UploadedFile
    {
        $thumbnailData = $this->downloadThumbnail($url);

        if (!$thumbnailData) {
            return null;
        }

        $tempFileName = $this->tempManager->generate();
        file_put_contents($tempFileName, $thumbnailData);

        return new UploadedFile($tempFileName, 'youtube_thumbnail.jpg', 'image/jpeg', null, true);
    }
}
