<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\DropZoneBundle\Entity\Correction;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.dropzone.correction")
 * @DI\Tag("claroline.serializer")
 */
class CorrectionSerializer
{
    private $gradeSerializer;
    private $userSerializer;

    private $correctionRepo;
    private $dropRepo;
    private $userRepo;

    /**
     * CorrectionSerializer constructor.
     *
     * @DI\InjectParams({
     *     "gradeSerializer" = @DI\Inject("claroline.serializer.dropzone.grade"),
     *     "userSerializer"  = @DI\Inject("claroline.serializer.user"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param GradeSerializer $gradeSerializer
     * @param UserSerializer  $userSerializer
     * @param ObjectManager   $om
     */
    public function __construct(
        GradeSerializer $gradeSerializer,
        UserSerializer $userSerializer,
        ObjectManager $om
    ) {
        $this->gradeSerializer = $gradeSerializer;
        $this->userSerializer = $userSerializer;

        $this->correctionRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Correction');
        $this->dropRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Drop');
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
    }

    /**
     * @param Correction $correction
     *
     * @return array
     */
    public function serialize(Correction $correction)
    {
        return [
            'id' => $correction->getUuid(),
            'drop' => $correction->getDrop()->getUuid(),
            'dropUser' => $correction->getDrop()->getUser()->getFullName(),
            'dropTeam' => $correction->getDrop()->getTeamName(),
            'user' => $correction->getUser() ? $this->userSerializer->serialize($correction->getUser()) : null,
            'score' => $correction->getScore(),
            'comment' => $correction->getComment(),
            'valid' => $correction->isValid(),
            'startDate' => $correction->getStartDate()->format('Y-m-d H:i:s'),
            'lastEditionDate' => $correction->getLastEditionDate()->format('Y-m-d H:i:s'),
            'endDate' => $correction->getEndDate() ? $correction->getEndDate()->format('Y-m-d H:i:s') : null,
            'finished' => $correction->isFinished(),
            'editable' => $correction->isEditable(),
            'reported' => $correction->isReported(),
            'reportedComment' => $correction->getReportedComment(),
            'correctionDenied' => $correction->isCorrectionDenied(),
            'correctionDeniedComment' => $correction->getCorrectionDeniedComment(),
            'teamId' => $correction->getTeamId(),
            'teamName' => $correction->getTeamName(),
            'grades' => $this->getGrades($correction),
        ];
    }

    /**
     * @param string $class
     * @param array  $data
     *
     * @return Correction
     */
    public function deserialize($class, $data)
    {
        $correction = $this->correctionRepo->findOneBy(['uuid' => $data['id']]);

        if (empty($correction)) {
            $correction = new Correction();
            $correction->setUuid($data['id']);
            $drop = $this->dropRepo->findOneBy(['uuid' => $data['drop']]);
            $correction->setDrop($drop);
            $currentDate = new \DateTime();
            $correction->setStartDate($currentDate);
            $correction->setLastEditionDate($currentDate);
        }
        if (isset($data['startDate'])) {
            $startDate = !empty($data['startDate']) ? new \DateTime($data['startDate']) : null;
            $correction->setStartDate($startDate);
        }
        if (isset($data['lastEditionDate'])) {
            $lastEditionDate = !empty($data['lastEditionDate']) ? new \DateTime($data['lastEditionDate']) : null;
            $correction->setLastEditionDate($lastEditionDate);
        }
        if (isset($data['user'])) {
            $user = isset($data['user']['id']) ? $this->userRepo->findOneBy(['id' => $data['user']['id']]) : null;
            $correction->setUser($user);
        }
        if (isset($data['endDate'])) {
            $endDate = !empty($data['endDate']) ? new \DateTime($data['endDate']) : null;
            $correction->setEndDate($endDate);
        }
        if (isset($data['score'])) {
            $correction->setScore($data['score']);
        }
        if (isset($data['comment'])) {
            $correction->setComment($data['comment']);
        }
        if (isset($data['valid'])) {
            $correction->setValid($data['valid']);
        }
        if (isset($data['finished'])) {
            $correction->setFinished($data['finished']);
        }
        if (isset($data['editable'])) {
            $correction->setEditable($data['editable']);
        }
        if (isset($data['reported'])) {
            $correction->setReported($data['reported']);
        }
        if (isset($data['reportedComment'])) {
            $correction->setReportedComment($data['reportedComment']);
        }
        if (isset($data['correctionDenied'])) {
            $correction->setCorrectionDenied($data['correctionDenied']);
        }
        if (isset($data['correctionDeniedComment'])) {
            $correction->setCorrectionDeniedComment($data['correctionDeniedComment']);
        }
        if (isset($data['teamId'])) {
            $correction->setTeamId($data['teamId']);
        }
        if (isset($data['teamName'])) {
            $correction->setTeamName($data['teamName']);
        }
        $this->deserializeGrades($correction, $data['grades']);

        return $correction;
    }

    private function getGrades(Correction $correction)
    {
        $grades = [];

        foreach ($correction->getGrades() as $grade) {
            $grades[] = $this->gradeSerializer->serialize($grade);
        }

        return $grades;
    }

    private function deserializeGrades(Correction $correction, $gradesData)
    {
        $correction->emptyGrades();

        foreach ($gradesData as $gradeData) {
            $gradeData['correction'] = $correction;
            $grade = $this->gradeSerializer->deserialize('Claroline\DropZoneBundle\Entity\Grade', $gradeData);
            $correction->addGrade($grade);
        }
    }
}
