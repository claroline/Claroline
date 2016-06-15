<?php

namespace Innova\CollecticielBundle\Controller;

use Innova\CollecticielBundle\Entity\Document;
use Innova\CollecticielBundle\Entity\Notation;
use Innova\CollecticielBundle\Entity\GradingCriteria;
use Innova\CollecticielBundle\Entity\Dropzone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ChoiceCriteriaController extends DropzoneBaseController
{
    /**
     * @ParamConverter("gradingCriteria", class="InnovaCollecticielBundle:GradingCriteria", options={"id" = "gradingCriteriaId"})
     * @ParamConverter("document", class="InnovaCollecticielBundle:Document", options={"id" = "documentId"})
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "dropzoneId"})
     * @Template()
     */
    public function renderChoiceTextAction(GradingCriteria $gradingCriteria, Document $document, Dropzone $dropzone)
    {
        $em = $this->getDoctrine()->getManager();

        $notation = $em->getRepository('InnovaCollecticielBundle:Notation')
                        ->findBy(
                                array(
                                    'document' => $document->getId(),
                                    'dropzone' => $dropzone->getId(),
                                     )
                                );

        // // Récupération de l'accusé de réceptoin
        $renderChoiceText = $em
        ->getRepository('InnovaCollecticielBundle:ChoiceCriteria')
        ->getChoiceTextForCriteriaAndNotation($gradingCriteria, $notation[0]);

        $choiceText = '';

        if (!empty($renderChoiceText)) {
            // Récupération de la valeur de l'accusé de réceptoin
            $choiceText = $renderChoiceText[0]->getChoiceText();
        }

        return array('value' => $choiceText);
    }
}
