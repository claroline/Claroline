<?php

namespace Claroline\BigBlueButtonBundle\Manager;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\CurlManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ServerManager
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var CurlManager */
    private $curlManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $config,
        CurlManager $curlManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->config = $config;
        $this->curlManager = $curlManager;
    }

    public function getServers(bool $onlyAvailable = true)
    {
        $available = [];

        $servers = $this->config->getParameter('bbb.servers');
        if (!empty($servers)) {
            foreach ($servers as $server) {
                if (!$onlyAvailable || !isset($server['disabled']) || !$server['disabled']) {
                    $available[] = [
                        'url' => $server['url'],
                        'limit' => isset($server['limit']) ? $server['limit'] : null,
                        'participants' => $this->countParticipants($server['url']),
                        'disabled' => isset($server['disabled']) ? $server['disabled'] : false,
                    ];
                }
            }
        }

        usort($available, function (array $a, array $b) {
            if ($a['participants'] === $b['participants']) {
                return 0;
            }

            return ($a['participants'] < $b['participants']) ? -1 : 1;
        });

        return array_values($available);
    }

    public function isAvailable(string $serverName)
    {
        $server = $this->getServer($serverName);
        if (empty($server['limit'])) {
            return true;
        }

        return $server['limit'] > $this->countParticipants($serverName);
    }

    public function countParticipants(string $serverName, string $tag = null): int
    {
        $meetings = $this->getMeetings($serverName, $tag);

        $count = 0;
        foreach ($meetings as $meeting) {
            $count += $meeting['participantCount'];
        }

        return $count;
    }

    public function getMeetings(string $serverName, string $tag = null): array
    {
        $meetings = [];

        $server = $this->getServer($serverName);
        if ($server) {
            $user = $this->tokenStorage->getToken()->getUser();

            $checksum = sha1("getMeetings${server['token']}");
            $url = "${server['url']}/bigbluebutton/api/getMeetings?checksum=$checksum";

            $response = $this->curlManager->exec($url);

            try {
                $dom = new \DOMDocument();
                if ($dom->loadXML($response)) {
                    $meetingsEl = $dom->getElementsByTagName('meeting');

                    for ($i = 0; $i < $meetingsEl->length; ++$i) {
                        $meetingEl = $meetingsEl->item($i);
                        $platform = $meetingEl->getElementsByTagName('platform')->item(0)->textContent;

                        if (empty($tag) || $tag === $platform) {
                            $meetingId = $meetingEl->getElementsByTagName('meetingID')->item(0)->textContent;

                            $moderatorPwd = null;
                            $joinUrl = null;
                            if ('anon.' !== $user) { // TODO : check against BBB rights
                                $moderatorPwd = $meetingEl->getElementsByTagName('moderatorPW')->item(0)->textContent;
                                $userId = $user->getUuid();
                                $userName = urlencode($user->getFirstName().' '.$user->getLastName());
                                $queryString = "meetingID=$meetingId&password=$moderatorPwd&userId=$userId&fullName=$userName&joinViaHtml5=true";
                                $check = sha1("join$queryString${server['token']}");
                                $joinUrl = "${server['url']}/bigbluebutton/api/join?$queryString&checksum=$check";
                            }

                            $meetings[] = array_merge($this->extractMeetingInfo($meetingEl), [
                                'url' => $joinUrl,
                                'server' => $server['url'],
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
            }
        }

        return $meetings;
    }

    public function getServer(string $serverName)
    {
        $server = null;

        $servers = $this->config->getParameter('bbb.servers');
        if (!empty($servers)) {
            foreach ($servers as $configuredServer) {
                if ($configuredServer['url'] === $serverName) {
                    $server = $configuredServer;
                    break;
                }
            }
        }

        return $server;
    }

    /**
     * @param \DOMDocument|\DomElement $meetingXml
     */
    public function extractMeetingInfo($meetingXml): array
    {
        $meetingId = $meetingXml->getElementsByTagName('meetingID')->item(0)->textContent;

        return [
            'meetingID' => $meetingId,
            'meetingName' => $meetingXml->getElementsByTagName('meetingName')->item(0)->textContent,
            'createTime' => $meetingXml->getElementsByTagName('createTime')->item(0)->textContent,
            'createDate' => $meetingXml->getElementsByTagName('createDate')->item(0)->textContent,
            'hasBeenForciblyEnded' => $meetingXml->getElementsByTagName('hasBeenForciblyEnded')->item(0)->textContent,
            'running' => (bool) $meetingXml->getElementsByTagName('running')->item(0)->textContent,
            'moderatorCount' => intval($meetingXml->getElementsByTagName('moderatorCount')->item(0)->textContent),
            'participantCount' => intval($meetingXml->getElementsByTagName('participantCount')->item(0)->textContent),
            'listenerCount' => $meetingXml->getElementsByTagName('listenerCount')->item(0)->textContent,
            'voiceParticipantCount' => $meetingXml->getElementsByTagName('voiceParticipantCount')->item(0)->textContent,
            'videoCount' => $meetingXml->getElementsByTagName('videoCount')->item(0)->textContent,
            'duration' => $meetingXml->getElementsByTagName('duration')->item(0)->textContent,
            'hasUserJoined' => $meetingXml->getElementsByTagName('hasUserJoined')->item(0)->textContent,
        ];
    }
}
