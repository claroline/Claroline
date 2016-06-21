<?php

namespace Innova\CollecticielBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Innova\CollecticielBundle\Entity\GradingScale;
use Innova\CollecticielBundle\Entity\Dropzone;

/**
 * @DI\Service("innova.manager.gradingscale_manager")
 */
class GradingScaleManager
{
    private $container;
    private $em;
    private $gradingScaleRepo;

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
        $this->gradingScaleRepo = $this->em->getRepository('InnovaCollecticielBundle:GradingScale');
        $this->notationRepo = $this->em->getRepository('InnovaCollecticielBundle:Notation');
    }

    /**
     *  To update gradingScale table.
     *
     * @param tab
     *
     * @return bool
     */
    public function manageGradingScales($tab, Dropzone $dropzone)
    {
        // handle old scales deletion
        $this->deleteOldScales($tab, $dropzone);
        // handle update and add

        // handle update and add
        $tabKeys = array_keys($tab);
        foreach ($tabKeys as $key) {
            // new
            if (empty($tab[$key]['id'])) {
                $gradingScaleData = $this->insertGradingScale($tab[$key]['scaleName'], $dropzone);
            } else {
                $gradingScale = $this->gradingScaleRepo->find($tab[$key]['id']);
                $gradingScaleData = $this->updateGradingScale($tab[$key]['scaleName'], $gradingScale);
            }

            $this->em->persist($gradingScaleData);
        }

        $this->em->flush();

        return true;
    }

    private function deleteOldScales($data,  Dropzone $dropzone)
    {
        $existing = $this->gradingScaleRepo->findByDropzone($dropzone);
        foreach ($existing as $scale) {
            $searchedId = $scale->getId();
            $found = false;
            foreach ($data as $value) {
                if ((int) $value['id'] === $searchedId) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->em->remove($scale);

                // Mise à jour de l'appréciation à zéro car le SCALE a été supprimé
                $notationsArray = $this->notationRepo
                    ->findBy(
                            array(
                                'appreciation' => $scale->getId(),
                                 )
                            );

                foreach ($notationsArray as $notation) {
                    $notation->setAppreciation(0);
                }
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
    public function insertGradingScale($scaleName, Dropzone $dropzone)
    {

        // Add a new grading Scale
        $gradingScale = new GradingScale();
        $gradingScale->setScaleName($scaleName);
        $gradingScale->setDropzone($dropzone);

        return $gradingScale;
    }

    /**
     *  To update gradingScale table.
     *
     * @param scaleName
     * @param Dropzone
     *
     * @return gradingScale
     */
    public function updateGradingScale($scaleName, GradingScale $gradingScale)
    {
        // update an existing grading Scale
        $gradingScale->setScaleName($scaleName);

        return $gradingScale;
    }
}
