<?php

namespace Claroline\PeerTubeBundle\Manager;

use Claroline\CoreBundle\Manager\CurlManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PeerTubeManager
{
    /** @var CurlManager */
    private $curlManager;

    public function __construct(CurlManager $curlManager)
    {
        $this->curlManager = $curlManager;
    }

    public function checkUrl(string $url): ?string
    {
        // Check if we can parse the given URL
        $urlParts = $this->extractUrlParts($url);
        if (empty($urlParts)) {
            return 'The URL is not a correct PeerTube URL.';
        }

        // Call PeerTube API to know if the ID exists and is accessible
        $uuid = null;
        try {
            $uuid = $this->getVideoUuid($urlParts['server'], $urlParts['shortUuid']);
        } catch (AccessDeniedException $e) {
            // the video requires authentication to be fetched
            return 'You do not have the right to access this video.';
        } catch (NotFoundHttpException $e) {
            // the url doesn't not exists
            return 'This video does not exist.';
        }

        if (empty($uuid)) {
            return 'This video does not exist.';
        }

        return null;
    }

    /**
     * Get the PeerTube server URL and the video short UUID from the share URL.
     */
    public function extractUrlParts(string $url): array
    {
        $parts = parse_url($url);
        if ($parts) {
            $server = $parts['scheme'].'://'.$parts['host'];
            $id = str_replace('/w/', '', $parts['path']);
            if (!empty($server) && !empty($id)) {
                return [
                    'server' => $server,
                    'shortUuid' => $id,
                ];
            }
        }

        return [];
    }

    public function getVideoUuid(string $server, string $shortUuid): ?string
    {
        $response = $this->curlManager->exec($server.'/api/v1/videos/'.$shortUuid);
        if (!empty($response)) {
            $result = json_decode($response, true);
            if (null === $result) {
                // not a json
                return null;
            }

            if (!empty($result['uuid'])) {
                return $result['uuid'];
            }
        }

        return null;
    }
}
