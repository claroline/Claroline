<?php

namespace Innova\CollecticielBundle\Controller;

use Innova\CollecticielBundle\Entity\Document;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Entity\GradingNotation;
use Innova\CollecticielBundle\Entity\Notation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ChoiceNotationController extends DropzoneBaseController
{
    /**
     * @ParamConverter("gradingNotation", class="InnovaCollecticielBundle:GradingNotation", options={"id" = "gradingNotationId"})
     * @ParamConverter("document", class="InnovaCollecticielBundle:Document", options={"id" = "documentId"})
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "dropzoneId"})
     * @Template()
     */
    public function renderChoiceTextAction(GradingNotation $gradingNotation, Document $document, Dropzone $dropzone)
    {
        $choiceText = '';

        // Appel pour accés base         
        $em = $this->getDoctrine()->getManager();

        // Ajout pour avoir si la notation a été transmise ou pas.
        $notationArray = $em->getRepository('InnovaCollecticielBundle:Notation')
                    ->findBy(
                            [
                                'document' => $document->getId(),
                                'dropzone' => $dropzone->getId(),
                            ]
                        );

        // Nombre de notation pour le document et pour le dropzone
        $countNotation = count($notationArray);

        if ($countNotation !== 0) {
            $choiceTextArray = $em->getRepository('InnovaCollecticielBundle:ChoiceNotation')
            ->getChoiceTextForCriteriaAndNotation($gradingNotation, $notationArray[0]);

            if (!empty($choiceTextArray)) {
                // Récupération de la valeur du texte
                $choiceText = $choiceTextArray[0]->getChoiceText();
            }
        }

        return ['value' => $choiceText];
    }
}
