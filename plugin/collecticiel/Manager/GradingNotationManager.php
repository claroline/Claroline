<?php

namespace Innova\CollecticielBundle\Manager;

use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Entity\GradingNotation;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("innova.manager.gradingnotation_manager")
 */
class GradingNotationManager
{
    private $container;
    private $em;
    private $gradingNotationRepo;

    /**
     * @DI\InjectParams({
     *     "container"  = @DI\Inject("service_container"),
     *     "em"         = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct($container, $em)
    {
        $this->container = $container;
        $this->em = $em;
        $this->gradingNotationRepo = $this->em->getRepository('InnovaCollecticielBundle:GradingNotation');
    }

    /**
     *  To update gradingScale table.
     *
     * @param tab
     *
     * @return bool
     */
    public function manageGradingNotations($tab, Dropzone $dropzone)
    {
        // handle old scales deletion
        $this->deleteOldNotations($tab, $dropzone);

        // handle update and add
        $tabKeys = array_keys($tab);
        foreach ($tabKeys as $key) {
            // new
            if (empty($tab[$key]['id'])) {
                if (!empty($tab[$key]['notationName'])) {
                    $gradingNotationData = $this->insertGradingNotation($tab[$key]['notationName'], $dropzone);
                    $this->em->persist($gradingNotationData);
                }
            } else {
                if (!empty($tab[$key]['notationName'])) {
                    $gradingNotation = $this->gradingNotationRepo->find($tab[$key]['id']);
                    $gradingNotationData = $this->updateGradingNotation($tab[$key]['notationName'], $gradingNotation);
                    $this->em->persist($gradingNotationData);
                }
            }
        }

        $this->em->flush();

        return true;
    }

    private function deleteOldNotations($data,  Dropzone $dropzone)
    {
        $existing = $this->gradingNotationRepo->findByDropzone($dropzone);

        foreach ($existing as $notation) {
            $searchedId = $notation->getId();
            $found = false;
            foreach ($data as $value) {
                if ((int) $value['id'] === $searchedId) {
                    if (!empty($value['notationName'])) {
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                $this->em->remove($notation);
            }
        }
    }

    /**
     *  To insert gradingNotation table.
     *
     * @param notationName
     * @param Dropzone
     *
     * @return gradingNotation
     */
    public function insertGradingNotation($notationName, Dropzone $dropzone)
    {
        echo '-';
        echo $notationName;
        echo '-';

        // Add a new grading Notation
        $gradingNotation = new GradingNotation();
        $gradingNotation->setNotationName($notationName);
        $gradingNotation->setDropzone($dropzone);

        return $gradingNotation;
    }

    /**
     *  To update gradingNotation table.
     *
     * @param notationName
     * @param Dropzone
     *
     * @return gradingNotation
     */
    public function updateGradingNotation($notationName, GradingNotation $gradingNotation)
    {
        // update an existing grading Scale
        $gradingNotation->setNotationName($notationName);

        return $gradingNotation;
    }
}
