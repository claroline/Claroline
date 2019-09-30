<?php

namespace Icap\DropzoneBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Resource\MaskManager;
use Icap\DropzoneBundle\Entity\Correction;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Event\Log\LogCorrectionUpdateEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.manager.correction_manager")
 */
class CorrectionManager
{
    private $container;
    private $maskManager;
    private $em;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *     "maskManager" = @DI\Inject("claroline.manager.mask_manager"),
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct($container, MaskManager $maskManager, $em)
    {
        $this->container = $container;
        $this->maskManager = $maskManager;
        $this->em = $em;
    }

    /**
     *  Calculate the grad of a copy.
     *
     * @param Dropzone   $dropzone
     * @param Correction $correction
     *
     * @return float|int
     */
    public function calculateCorrectionTotalGrade(Dropzone $dropzone, Correction $correction)
    {
        $nbCriteria = count($dropzone->getPeerReviewCriteria());
        $maxGrade = $dropzone->getTotalCriteriaColumn() - 1;
        $sumGrades = 0;
        foreach ($correction->getGrades() as $grade) {
            ($grade->getValue() > $maxGrade) ? $sumGrades += $maxGrade : $sumGrades += $grade->getValue();
        }

        $totalGrade = 0;
        if (0 !== $nbCriteria) {
            $totalGrade = $sumGrades / ($nbCriteria);
            $totalGrade = ($totalGrade * 20) / ($maxGrade);
        }

        return $totalGrade;
    }

    public function recalculateScoreForCorrections(Dropzone $dropzone, array $corrections)
    {
        $this->container->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);
        // recalculate the score for all corrections
        foreach ($corrections as $correction) {
            $oldTotalGrade = $correction->getTotalGrade();
            $totalGrade = $this->calculateCorrectionTotalGrade($dropzone, $correction);
            $correction->setTotalGrade($totalGrade);
            $em = $this->em;

            $em->persist($correction);
            $em->flush();

            $currentDrop = $correction->getDrop();
            if (null !== $currentDrop && $oldTotalGrade !== $totalGrade) {
                $event = new LogCorrectionUpdateEvent($dropzone, $currentDrop, $correction);
                $this->container->get('event_dispatcher')->dispatch('log', $event);
            }
        }
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function replaceUser(User $from, User $to)
    {
        $corrections = $this->em->getRepository('IcapDropzoneBundle:Correction')->findByUser($from);

        if (count($corrections) > 0) {
            foreach ($corrections as $correction) {
                $correction->setUser($to);
            }

            $this->em->flush();
        }

        return count($corrections);
    }
}
