<?php

namespace Innova\CollecticielBundle\Manager;

use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Entity\GradingCriteria;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("innova.manager.gradingcriteria_manager")
 */
class GradingCriteriaManager
{
    private $container;
    private $em;
    private $gradingCriteriaRepo;

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
        $this->gradingCriteriaRepo = $this->em->getRepository('InnovaCollecticielBundle:GradingCriteria');
    }

    /**
     *  To update gradingScale table.
     *
     * @param tab
     *
     * @return bool
     */
    public function manageGradingCriterias($tab, Dropzone $dropzone)
    {
        // handle old scales deletion
        $this->deleteOldCriterias($tab, $dropzone);

        // handle update and add
        $tabKeys = array_keys($tab);
        foreach ($tabKeys as $key) {
            // new
            if (empty($tab[$key]['id'])) {
                if (!empty($tab[$key]['criteriaName'])) {
                    $gradingCriteriaData = $this->insertGradingCriteria($tab[$key]['criteriaName'], $dropzone);
                }
            } else {
                if (!empty($tab[$key]['criteriaName'])) {
                    $gradingCriteria = $this->gradingCriteriaRepo->find($tab[$key]['id']);
                    $gradingCriteriaData = $this->updateGradingCriteria($tab[$key]['criteriaName'], $gradingCriteria);
                }
            }

            $this->em->persist($gradingCriteriaData);
        }

        $this->em->flush();

        return true;
    }

    private function deleteOldCriterias($data,  Dropzone $dropzone)
    {
        $existing = $this->gradingCriteriaRepo->findByDropzone($dropzone);

        foreach ($existing as $criteria) {
            $searchedId = $criteria->getId();
            $found = false;
            foreach ($data as $value) {
                if ((int) $value['id'] === $searchedId) {
                    if (!empty($value['criteriaName'])) {
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                $this->em->remove($criteria);
            }
        }
    }

    /**
     *  To insert gradingScale table.
     *
     * @param scaleName
     * @param Dropzone
     *
     * @return gradingScale
     */
    public function insertGradingCriteria($criteriaName, Dropzone $dropzone)
    {

        // Add a new grading Scale
        $gradingCriteria = new GradingCriteria();
        $gradingCriteria->setCriteriaName($criteriaName);
        $gradingCriteria->setDropzone($dropzone);

        return $gradingCriteria;
    }

    /**
     *  To update gradingScale table.
     *
     * @param scaleName
     * @param Dropzone
     *
     * @return gradingScale
     */
    public function updateGradingCriteria($criteriaName, GradingCriteria $gradingCriteria)
    {
        // update an existing grading Scale
        $gradingCriteria->setCriteriaName($criteriaName);

        return $gradingCriteria;
    }
}
