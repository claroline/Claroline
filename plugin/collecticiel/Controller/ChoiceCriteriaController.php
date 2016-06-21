<?php

namespace Innova\CollecticielBundle\Controller;

use Innova\CollecticielBundle\Entity\Document;
use Innova\CollecticielBundle\Entity\GradingCriteria;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Entity\Notation;
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
        $choiceText = '';

        // Appel pour accés base         
        $em = $this->getDoctrine()->getManager();

        // Ajout pour avoir si la notation a été transmise ou pas.
        $notationArray = $em->getRepository('InnovaCollecticielBundle:Notation')
                    ->findBy(
                            array(
                                'document' => $document->getId(),
                                'dropzone' => $dropzone->getId(),
                                 )
                            );

        // Nombre de notation pour le document et pour le dropzone
        $countNotation = count($notationArray);

        if ($countNotation != 0) {
            $choiceTextArray = $em->getRepository('InnovaCollecticielBundle:ChoiceCriteria')
            ->getChoiceTextForCriteriaAndNotation($gradingCriteria, $notationArray[0]);

            if (!empty($choiceTextArray)) {
                // Récupération de la valeur du texte
                $choiceText = $choiceTextArray[0]->getChoiceText();
            }
        }

        return array('value' => $choiceText);
    }
}
