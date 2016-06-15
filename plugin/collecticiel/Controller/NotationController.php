<?php
/**
 * Created by : Eric VINCENT
 * Date: 05/16.
 */

namespace Innova\CollecticielBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Entity\Document;
use Innova\CollecticielBundle\Entity\Notation;
use Innova\CollecticielBundle\Entity\ChoiceCriteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

class NotationController extends DropzoneBaseController
{
    /**
     * @Route(
     *      "/add/notation",
     *      name="innova_collecticiel_add_notation",
     *      options={"expose"=true}
     * )
     * @Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function AddNotationForDocsAction()
    {

        // Récupération de l'ID du document
        $documentId = $this->get('request')->query->get('documentId');
        $dropzoneId = $this->get('request')->query->get('dropzoneId');
        $note = $this->get('request')->query->get('note');
//        $commentText = $this->get('request')->query->get('commentText');
//        $qualityText = $this->get('request')->query->get('qualityText');
        $appreciation = $this->get('request')->query->get('appreciation');
        $recordOrTransmit = $this->get('request')->query->get('recordOrTransmit');

        $em = $this->getDoctrine()->getManager();
        $dropzone = $em->getRepository('InnovaCollecticielBundle:Dropzone')->find($dropzoneId);
        $document = $em->getRepository('InnovaCollecticielBundle:Document')->find($documentId);

        // Récupération de l'utilisateur
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);

        // Récupération des documents sélectionnés
        $arrayCriteriaId = $this->get('request')->query->get('arrayCriteriaId');
        $arrayCriteriaIdToView = array();

        $arrayCriteriaName = $this->get('request')->query->get('arrayCriteriaName');
        $arrayCriteriaNameToView = array();

        $arrayCriteriaValue = $this->get('request')->query->get('arrayCriteriaValue');
        $arrayCriteriaValueToView = array();

        // Pour insérer l'ID de la table Notation dans la tabke Choice_criteria
        $notationId = 0;

        if ($recordOrTransmit == 0) {
            // Ajout pour avoir si la notation a été transmise ou pas.
            $notation = $em->getRepository('InnovaCollecticielBundle:Notation')
                        ->findBy(
                                array(
                                    'document' => $document->getId(),
                                    'dropzone' => $dropzone->getId(),
                                     )
                                );

            if (!empty($notation)) {
                $notation[0]->setNote($note);
                $notation[0]->setappreciation($appreciation);
                // Mise à jour de la base de données
                $em->persist($notation[0]);
                $notationId = $notation[0]->getId();
            } else {
                // Valorisation de l'évaluation/notation
                $notation = new Notation();
                $notation->setUser($user);
                $notation->setDocument($document);
                $notation->setDropzone($dropzone);
                $notation->setNote($note);
                $notation->setCommentText('');
                $notation->setQualityText('');
                $notation->setRecordOrTransmit($recordOrTransmit);
                $notation->setappreciation($appreciation);

                // Insertion en base
                $em->persist($notation);
            }
        } else {
            // Ajout pour avoir si la notation a été transmise ou pas.
            $notation = $em->getRepository('InnovaCollecticielBundle:Notation')
                        ->findBy(
                                array(
                                    'document' => $document->getId(),
                                    'dropzone' => $dropzone->getId(),
                                     )
                                );

            $notation[0]->setRecordOrTransmit(true);
            $notation[0]->setappreciation($appreciation);
            // Mise à jour de la base de données
            $em->persist($notation[0]);
            $notationId = $notation[0]->getId();
        }

        $em->flush();

        if ($notationId == 0) {
            // Ajout pour avoir si la notation a été transmise ou pas.
            $notation = $em->getRepository('InnovaCollecticielBundle:Notation')
                        ->findBy(
                                array(
                                    'document' => $document->getId(),
                                    'dropzone' => $dropzone->getId(),
                                     )
                                );
            $notationId = $notation[0]->getId();
        }

        // Insertion dans la table ChoiceCriteria.
        // Parcours des documents sélectionnés et insertion en base de données
        if (!empty($arrayCriteriaId)) {
            $cpt = 0;
            foreach ($arrayCriteriaId as $criteriaId) {
                $gradingCriteria = $em->getRepository('InnovaCollecticielBundle:GradingCriteria')->find($criteriaId);

            // Ajout pour avoir si la notation a été transmise ou pas.
                $choiceCriteriaArray = $em->getRepository('InnovaCollecticielBundle:choiceCriteria')
                            ->findBy(
                                    array(
                                        'notation' => $notationId,
                                        'gradingCriteria' => $criteriaId,
                                         )
                                    );

                // Nombre de notation pour le document et pour le dropzone
                $countExistCriteria = count($choiceCriteriaArray);

                if ($countExistCriteria == 0) {
                    $choiceCriteria = new ChoiceCriteria();
                    $choiceCriteria->setGradingCriteria($gradingCriteria);
                    $choiceCriteria->setNotation($notation[0]);
                    $choiceCriteria->setChoiceText($arrayCriteriaValue[$cpt]);
                    // Insertion en base
                    $em->persist($choiceCriteria);

                    ++$cpt;
                } else {
                    // $choiceCriteria = $em->getRepository('InnovaCollecticielBundle:choiceCriteria')
                    //   ->find($choiceCriteriaArray[0]->getId());
                    // $choiceCriteria->setChoiceText($arrayCriteriaValue[$cpt]);
                    // // Insertion en base
                    // $em->persist($choiceCriteria);
                    // ++$cpt;
                }
            }
        }

        // // Parcours des documents sélectionnés et insertion en base de données
        // if (!empty($arrayCriteriaName)) {
        //     foreach ($arrayCriteriaName as $criteriaName) {
        //     }
        // }

        // // Parcours des documents sélectionnés et insertion en base de données
        // if (!empty($arrayCriteriaValue)) {
        //     foreach ($arrayCriteriaValue as $criteriaValue) {
        //     }
        // }

        $em->flush();

        // Redirection
        $url = $this->generateUrl('innova_collecticiel_drops_awaiting', array(
                    'resourceId' => $dropzone->getId(),
                )
        );

        return new JsonResponse(array('link' => $url));
    }

    /**
     * @Route(
     *      "/document/{documentId}/dropzone/{dropzoneId}",
     *      name="innova_collecticiel_validate_transmit_evaluation",
     *      requirements={"documentId" = "\d+", "dropzoneId" = "\d+"},
     *      options={"expose"=true}
     * )
     * @ParamConverter("document", class="InnovaCollecticielBundle:Document", options={"id" = "documentId"})
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "dropzoneId"})
     * @Template()
     */
    public function ajaxValidateTransmitEvaluationDocumentAction(Document $document, Dropzone $dropzone)
    {

        // Appel pour accés base         
        $em = $this->getDoctrine()->getManager();

        // Recherche en base des données du document à mettre à jour
        $document = $em->getRepository('InnovaCollecticielBundle:Document')->find($document->getId());

        $dropzone = $em->getRepository('InnovaCollecticielBundle:DropZone')->find($dropzone->getId());

        $drop = $em->getRepository('InnovaCollecticielBundle:Drop')->find($document->getDrop());

        // Ajout pour avoir si la notation a été transmise ou pas.
        $notation = $em->getRepository('InnovaCollecticielBundle:Notation')
                    ->findBy(
                            array(
                                'document' => $document->getId(),
                                'dropzone' => $dropzone->getId(),
                                 )
                            );

        $notation[0]->setRecordOrTransmit(true);

        // Mise à jour de la base de données
        $em->persist($notation[0]);
        $em->flush();

        $gradingScale = $em->getRepository('InnovaCollecticielBundle:GradingScale')
                      ->find($notation[0]->getAppreciation());

        // Ajout afin d'afficher la partie du code avec "Demande transmise"
        $template = $this->get('templating')->
        render('InnovaCollecticielBundle:Document:documentIsTransmit.html.twig',
                array('document' => $document,
                      'dropzone' => $dropzone,
                      'drop' => $drop,
                      'recordOrTransmitNotation' => 1,
                      'notationDocument' => $notation[0]->getNote(),
                      'maximumNotation' => $dropzone->getMaximumNotation(),
                      'notationCommentDocument' => $notation[0]->getCommentText(),
                      'notationQualityDocument' => $notation[0]->getQualityText(),
                      'notationScaleDocument' => $gradingScale->getScaleName(),
                    )
               );

        // Retour du template actualisé à l'Ajax et non plus du Json.
        return new Response($template);
    }
}
