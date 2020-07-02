<?php

namespace Claroline\BigBlueButtonBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BigBlueButtonBundle\Entity\BBB;
use Claroline\BigBlueButtonBundle\Repository\BBBRepository;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\CurlManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BBBManager
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var TranslatorInterface */
    private $translator;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var ObjectManager */
    private $om;
    /** @var CurlManager */
    private $curlManager;
    /** @var RoutingHelper */
    private $routingHelper;
    /** @var ServerManager */
    private $serverManager;

    /** @var BBBRepository */
    private $bbbRepo;

    /**
     * @param TokenStorageInterface        $tokenStorage
     * @param TranslatorInterface          $translator
     * @param PlatformConfigurationHandler $config
     * @param ObjectManager                $om
     * @param CurlManager                  $curlManager
     * @param RoutingHelper                $routingHelper
     * @param ServerManager                $serverManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        PlatformConfigurationHandler $config,
        ObjectManager $om,
        CurlManager $curlManager,
        RoutingHelper $routingHelper,
        ServerManager $serverManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->config = $config;
        $this->om = $om;
        $this->curlManager = $curlManager;
        $this->routingHelper = $routingHelper;
        $this->serverManager = $serverManager;

        $this->bbbRepo = $this->om->getRepository(BBB::class);
    }

    public function getMeetingUrl(BBB $bbb, bool $moderator = false, string $username = null)
    {
        $user = null;
        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
            $user = $this->tokenStorage->getToken()->getUser();
        }

        $meetingId = $bbb->getUuid();
        $server = $this->getMeetingServer($bbb);
        $serverUrl = $server['url'];
        $securitySalt = $server['token'];

        $url = '';
        if ($serverUrl && $securitySalt) {
            if ($user) {
                $userId = $user->getUuid();
                $name = $user->getFullName();
            } else {
                $userId = Uuid::uuid4()->toString();
                $name = $this->translator->trans('anonymous').'_'.$userId;
            }

            if ($bbb->hasCustomUsernames() && !empty($username)) {
                $name = $username;
            }

            $name = urlencode($name);
            $password = $moderator ? 'manager' : 'collaborator';
            $queryString = "meetingID=$meetingId&password=$password&userId=${userId}&fullName=$name";
            $checksum = sha1("join$queryString$securitySalt");

            $url = "$serverUrl/bigbluebutton/api/join?$queryString&checksum=$checksum";
        }

        return $url;
    }

    public function canStartMeeting(BBB $bbb)
    {
        $isRunning = $this->isMeetingRunning($bbb);
        if (!$isRunning) {
            $meetings = $this->fetchActiveMeetingsWithParticipants();
            $maxMeetings = $this->config->getParameter('bbb.max_meetings');

            if ($maxMeetings && count($meetings) >= $maxMeetings) {
                return false;
            }
        }

        return true;
    }

    public function canJoinMeeting(BBB $bbb, bool $moderator = false)
    {
        $info = $this->getMeetingInfo($bbb);
        if (!$bbb->isActivated() || (empty($info) || !isset($info['running']) || !$info['running'])) {
            return 'closed';
        }

        if (!$moderator && $bbb->isModeratorRequired() && empty($info['moderatorCount'])) {
            return 'no_moderator';
        }

        $maxMeetingParticipants = $this->config->getParameter('bbb.max_meeting_participants');
        if ($maxMeetingParticipants && $info['participantCount'] >= $maxMeetingParticipants) {
            return 'max_meeting_participants_reached';
        }

        $maxMeetingParticipants = $this->config->getParameter('bbb.max_participants');
        if ($maxMeetingParticipants && $this->countParticipants() >= $maxMeetingParticipants) {
            return 'max_platform_participants_reached';
        }

        if (!$this->serverManager->isAvailable($bbb->getRunningOn())) {
            return 'max_server_participants_reached';
        }

        return null;
    }

    public function createMeeting(BBB $bbb)
    {
        $success = false;

        $server = $this->getMeetingServer($bbb);
        $serverUrl = $server['url'];
        $securitySalt = $server['token'];
        $maxParticipants = $this->config->getParameter('bbb.max_meeting_participants');
        $tag = $this->config->getParameter('mailer.tag'); // FIXME

        if ($serverUrl && $securitySalt) {
            $resourceNode = $bbb->getResourceNode();
            $now = new \DateTime();
            $endDate = $resourceNode->getAccessibleUntil();

            if (!$endDate || $now < $endDate) {
                $duration = $endDate ? ceil(abs($now->getTimestamp() - $endDate->getTimestamp()) / 60) : null;

                if (0 === $duration) {
                    $duration = 1;
                }
                $meetingId = $bbb->getUuid();
                $record = $this->config->getParameter('bbb.allow_records') && $bbb->isRecord();
                $roomName = $resourceNode->getName();
                $welcomeMessage = $bbb->getWelcomeMessage();
                $endUrl = $this->routingHelper->resourceUrl($resourceNode);

                $queryString = "meetingID=$meetingId&attendeePW=collaborator&moderatorPW=manager";
                $queryString .= $record ? '&record=true' : '&record=false';
                $queryString .= $duration ? "&duration=$duration" : '';
                $queryString .= $roomName ? '&name='.urlencode($roomName) : '';
                $queryString .= $welcomeMessage ? '&welcome='.urlencode($welcomeMessage) : '';
                $queryString .= $maxParticipants ? '&maxParticipants='.$maxParticipants : '';
                $queryString .= '&logoutURL='.urlencode($endUrl);
                $queryString .= $tag ? '&meta_platform='.$tag : '';
                $checksum = sha1("create$queryString$securitySalt");
                $url = "$serverUrl/bigbluebutton/api/create?$queryString&checksum=$checksum";

                $response = $this->curlManager->exec($url);

                $dom = new \DOMDocument();
                if ($response && $dom->loadXML($response)) {
                    $returnCodes = $dom->getElementsByTagName('returncode');
                    $success = $returnCodes->length > 0 && 'SUCCESS' === $returnCodes->item(0)->textContent;
                }
            }
        }

        if ($success) {
            $bbb->setRunningOn($serverUrl);

            $this->om->persist($bbb);
            $this->om->flush();
        }

        return $success;
    }

    /**
     * @param BBB $bbb
     */
    public function endMeeting(BBB $bbb)
    {
        $meetingId = $bbb->getUuid();

        $server = $this->getMeetingServer($bbb);
        $serverUrl = $server['url'];
        $securitySalt = $server['token'];

        if ($serverUrl && $securitySalt) {
            $queryString = "meetingID=$meetingId&password=manager";
            $checksum = sha1("end$queryString$securitySalt");
            $url = "$serverUrl/bigbluebutton/api/end?$queryString&checksum=$checksum";

            $this->curlManager->exec($url);
        }
    }

    /**
     * @param BBB $bbb
     *
     * @return array
     */
    public function getMeetingInfo(BBB $bbb)
    {
        $info = null;

        $meetingId = $bbb->getUuid();
        $server = $this->getMeetingServer($bbb);
        $serverUrl = $server['url'];
        $securitySalt = $server['token'];

        if ($serverUrl && $securitySalt) {
            $queryString = "meetingID=$meetingId&password=manager";
            $checksum = sha1("getMeetingInfo$queryString$securitySalt");
            $url = "$serverUrl/bigbluebutton/api/getMeetingInfo?$queryString&checksum=$checksum";

            $response = $this->curlManager->exec($url);

            try {
                $dom = new \DOMDocument();
                if ($dom->loadXML($response)) {
                    $info = $this->serverManager->extractMeetingInfo($dom);
                }
            } catch (\Exception $e) {
            }
        }

        return $info;
    }

    /**
     * @param BBB $bbb
     *
     * @return bool
     */
    public function isMeetingRunning(BBB $bbb)
    {
        $info = $this->getMeetingInfo($bbb);
        if (!empty($info) && isset($info['running']) && $info['running']) {
            return true;
        }

        return false;
    }

    public function hasMeetingModerators(BBB $bbb)
    {
        $info = $this->getMeetingInfo($bbb);
        if (!empty($info) && isset($info['moderatorCount']) && 0 > $info['moderatorCount']) {
            return true;
        }

        return false;
    }

    public function countParticipants()
    {
        $count = 0;

        $tag = $this->config->getParameter('mailer.tag'); // FIXME
        $servers = $this->bbbRepo->findUsedServers();
        foreach ($servers as $server) {
            $count += $this->serverManager->countParticipants($server, $tag);
        }

        return $count;
    }

    /**
     * @return array
     */
    public function fetchActiveMeetings()
    {
        $meetings = [];

        $tag = $this->config->getParameter('mailer.tag'); // FIXME
        $servers = $this->bbbRepo->findUsedServers();
        foreach ($servers as $server) {
            $meetings = array_merge($meetings, $this->serverManager->getMeetings($server, $tag));
        }

        return $meetings;
    }

    /**
     * @return array
     */
    public function fetchActiveMeetingsWithParticipants()
    {
        $meetingsWithParticipants = [];
        $meetings = $this->fetchActiveMeetings();

        foreach ($meetings as $meeting) {
            if (0 < $meeting['participantCount']) {
                $meetingsWithParticipants[] = $meeting;
            }
        }

        return $meetingsWithParticipants;
    }

    /**
     * @param BBB $bbb
     *
     * @return array
     */
    public function getRecordings(BBB $bbb)
    {
        $recordings = [];

        $meetingId = $bbb->getUuid();
        $server = $this->getMeetingServer($bbb);
        $serverUrl = $server['url'];
        $securitySalt = $server['token'];

        if ($serverUrl && $securitySalt) {
            $queryString = "meetingID=$meetingId&state=processing,processed,published,unpublished";
            $checksum = sha1("getRecordings$queryString$securitySalt");
            $url = "$serverUrl/bigbluebutton/api/getRecordings?$queryString&checksum=$checksum";

            $response = $this->curlManager->exec($url);

            $dom = new \DOMDocument();
            if ($dom->loadXML($response)) {
                $recordingsEl = $dom->getElementsByTagName('recording');

                for ($i = 0; $i < $recordingsEl->length; ++$i) {
                    $recordingEl = $recordingsEl->item($i);
                    $media = [];
                    $playbackEl = $recordingEl->getElementsByTagName('playback')->item(0);
                    $formatsEl = $playbackEl->getElementsByTagName('format');

                    for ($j = 0; $j < $formatsEl->length; ++$j) {
                        $formatEl = $formatsEl->item($j);
                        $type = $formatEl->getElementsByTagName('type')->item(0)->textContent;
                        $media[$type] = $formatEl->getElementsByTagName('url')->item(0)->textContent;
                    }

                    $recordings[] = [
                        'recordID' => $recordingEl->getElementsByTagName('recordID')->item(0)->textContent,
                        'meetingID' => $recordingEl->getElementsByTagName('meetingID')->item(0)->textContent,
                        'name' => $recordingEl->getElementsByTagName('name')->item(0)->textContent,
                        'state' => $recordingEl->getElementsByTagName('state')->item(0)->textContent,
                        'startTime' => (int) $recordingEl->getElementsByTagName('startTime')->item(0)->textContent,
                        'endTime' => (int) $recordingEl->getElementsByTagName('endTime')->item(0)->textContent,
                        'participants' => intval($recordingEl->getElementsByTagName('participants')->item(0)->textContent),
                        'media' => $media,
                    ];
                }
            }
        }

        // sort by date
        usort($recordings, function (array $a, array $b) {
            return ($a['startTime'] >= $b['endTime']) ? -1 : 1;
        });

        return $recordings;
    }

    public function getLastRecording(BBB $bbb)
    {
        $recordings = $this->getRecordings($bbb);
        if (!empty($recordings)) {
            return $recordings[0];
        }

        return null;
    }

    /**
     * @param BBB   $bbb
     * @param array $recordIds - the list of records to delete. If empty, it will delete all the $bbb recordings.
     */
    public function deleteMeetingRecordings(BBB $bbb, array $recordIds = [])
    {
        if (empty($recordIds)) {
            // grab all recordings
            $recordings = $this->getRecordings($bbb);
            if (0 < count($recordings)) {
                foreach ($recordings as $recording) {
                    $recordIds[] = $recording['recordID'];
                }
            }
        }

        if (!empty($recordIds)) {
            $server = $this->getMeetingServer($bbb);
            $serverUrl = $server['url'];
            $securitySalt = $server['token'];
            if ($serverUrl && $securitySalt) {
                $ids = implode(',', $recordIds);
                $queryString = "recordID=$ids";
                $checksum = sha1("deleteRecordings$queryString$securitySalt");
                $url = "$serverUrl/bigbluebutton/api/deleteRecordings?$queryString&checksum=$checksum";

                $this->curlManager->exec($url);
            }
        }
    }

    private function getMeetingServer(BBB $bbb)
    {
        $server = null;
        if ($bbb->getRunningOn()) {
            $server = $bbb->getRunningOn();
        } elseif ($bbb->getServer()) {
            $server = $bbb->getServer();
        } else {
            $available = $this->serverManager->getAvailableServers();
            if (!empty($available)) {
                $server = $available[0];
            }
        }

        return $this->serverManager->getServer($server);
    }
}
