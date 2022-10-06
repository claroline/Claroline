<?php

namespace Claroline\DropZoneBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\DropZoneBundle\Entity\Correction;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Repository\DropRepository;

class DropManager
{
    /** @var ObjectManager */
    private $om;
    /** @var DocumentManager */
    private $documentManager;

    /** @var DropRepository */
    private $dropRepo;

    public function __construct(
        ObjectManager $om,
        DocumentManager $documentManager
    ) {
        $this->om = $om;
        $this->documentManager = $documentManager;

        $this->dropRepo = $om->getRepository(Drop::class);
    }

    /**
     * Gets all drops for given Dropzone.
     *
     * @return array
     */
    public function getAllDrops(Dropzone $dropzone)
    {
        return $this->dropRepo->findBy(['dropzone' => $dropzone]);
    }

    /**
     * Gets Team drops or create one.
     *
     * @return array
     */
    public function getTeamDrops(Dropzone $dropzone, User $user)
    {
        return $this->dropRepo->findTeamDrops($dropzone, $user);
    }

    /**
     * Gets drops corrected by user|team but that are not finished.
     *
     * @param User   $user
     * @param string $teamId
     *
     * @return array
     */
    private function getUnfinishedPeerDrops(Dropzone $dropzone, User $user = null, $teamId = null)
    {
        $drops = [];

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                if (!empty($user)) {
                    $drops = $this->dropRepo->findUserUnfinishedPeerDrop($dropzone, $user);
                }
                break;
            case Dropzone::DROP_TYPE_TEAM:
                if ($teamId) {
                    $drops = $this->dropRepo->findTeamUnfinishedPeerDrop($dropzone, $teamId);
                }
                break;
        }

        return $drops;
    }

    /**
     * Gets user|team drop if it is finished.
     */
    private function getFinishedUserDrop(Dropzone $dropzone, ?User $user = null, ?string $teamId = null): Drop
    {
        $drop = null;

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                if (!empty($user)) {
                    $drop = $this->dropRepo->findOneBy([
                        'dropzone' => $dropzone,
                        'user' => $user,
                        'teamUuid' => null,
                        'finished' => true,
                    ]);
                }
                break;
            case Dropzone::DROP_TYPE_TEAM:
                if ($teamId) {
                    $drop = $this->dropRepo->findOneBy(['dropzone' => $dropzone, 'teamUuid' => $teamId, 'finished' => true]);
                }
                break;
        }

        return $drop;
    }

    /**
     * Gets a drop for peer evaluation.
     *
     * @param User   $user
     * @param string $teamId
     * @param string $teamName
     * @param bool   $withCreation
     *
     * @return Drop|null
     */
    public function getPeerDrop(Dropzone $dropzone, User $user = null, $teamId = null, $teamName = null, $withCreation = true)
    {
        $peerDrop = null;

        /* Gets user|team drop to check if it is finished before allowing peer review */
        $userDrop = $this->getFinishedUserDrop($dropzone, $user, $teamId);

        /* user|team drop is finished */
        if (!empty($userDrop)) {
            /* Gets drops where user|team has an unfinished correction */
            $unfinishedDrops = $this->getUnfinishedPeerDrops($dropzone, $user, $teamId);

            if (count($unfinishedDrops) > 0) {
                /* Returns the first drop with an unfinished correction */
                $peerDrop = $unfinishedDrops[0];
            } else {
                /* Gets drops where user|team has a finished correction */
                $finishedDrops = $this->getFinishedPeerDrops($dropzone, $user, $teamId);
                $nbCorrections = count($finishedDrops);

                /* Fetches a drop for peer correction if user|team has not made the expected number of corrections */
                if ($withCreation && $dropzone->isReviewEnabled() && $nbCorrections < $dropzone->getExpectedCorrectionTotal()) {
                    $peerDrop = $this->getAvailableDropForPeer($dropzone, $user, $teamId, $teamName);
                }
            }
        }

        return $peerDrop;
    }

    /**
     * Gets unfinished drops from teams list.
     *
     * @return array
     */
    public function getTeamsUnfinishedDrops(Dropzone $dropzone, array $teamsIds)
    {
        return $this->dropRepo->findTeamsUnfinishedDrops($dropzone, $teamsIds);
    }

    /**
     * Gets drops corrected by user|team.
     *
     * @param User   $user
     * @param string $teamId
     *
     * @return array
     */
    public function getFinishedPeerDrops(Dropzone $dropzone, User $user = null, $teamId = null)
    {
        $drops = [];

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                if (!empty($user)) {
                    $drops = $this->dropRepo->findUserFinishedPeerDrops($dropzone, $user);
                }
                break;
            case Dropzone::DROP_TYPE_TEAM:
                if ($teamId) {
                    $drops = $this->dropRepo->findTeamFinishedPeerDrops($dropzone, $teamId);
                }
                break;
        }

        return $drops;
    }

    /**
     * Gets available drop for peer evaluation.
     *
     * @param User   $user
     * @param string $teamId
     * @param string $teamName
     *
     * @return Drop|null
     */
    public function getAvailableDropForPeer(Dropzone $dropzone, User $user = null, $teamId = null, $teamName = null)
    {
        $peerDrop = null;
        $drops = [];

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                if (!empty($user)) {
                    $drops = $this->dropRepo->findUserAvailableDrops($dropzone, $user);
                }
                break;
            case Dropzone::DROP_TYPE_TEAM:
                if ($teamId) {
                    $drops = $this->dropRepo->findTeamAvailableDrops($dropzone, $teamId);
                }
                break;
        }
        $validDrops = [];

        foreach ($drops as $drop) {
            $corrections = $drop->getCorrections();

            if (count($corrections) < $dropzone->getExpectedCorrectionTotal()) {
                $validDrops[] = $drop;
            }
        }
        if (count($validDrops) > 0) {
            /* Selects the drop with the least corrections */
            $peerDrop = $this->getDropWithTheLeastCorrections($validDrops);

            /* Creates empty correction */
            $correction = new Correction();
            $correction->setDrop($peerDrop);
            $correction->setUser($user);
            $correction->setTeamUuid($teamId);
            $correction->setTeamName($teamName);
            $currentDate = new \DateTime();
            $correction->setStartDate($currentDate);
            $correction->setLastEditionDate($currentDate);
            $peerDrop->addCorrection($correction);
            $this->om->persist($correction);
            $this->om->flush();
        }

        return $peerDrop;
    }

    /**
     * Deletes a Drop.
     *
     * @deprecated use crud instead
     */
    public function deleteDrop(Drop $drop): void
    {
        $this->om->startFlushSuite();

        $documents = $drop->getDocuments();
        foreach ($documents as $document) {
            $this->documentManager->deleteDocument($document);
        }

        $this->om->remove($drop);
        $this->om->endFlushSuite();
    }

    private function getDropWithTheLeastCorrections(array $drops): ?Drop
    {
        $selectedDrop = count($drops) > 0 ? $drops[0] : null;
        $min = !empty($selectedDrop) ? count($selectedDrop->getCorrections()) : null;

        foreach ($drops as $drop) {
            $nbCorrections = count($drop->getCorrections());

            if ($nbCorrections < $min) {
                $selectedDrop = $drop;
                $min = $nbCorrections;
            }
        }

        return $selectedDrop;
    }
}
