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
        $commentText = $this->get('request')->query->get('commentText');
        $qualityText = $this->get('request')->query->get('qualityText');
        $recordOrTransmit = $this->get('request')->query->get('recordOrTransmit');

        $em = $this->getDoctrine()->getManager();
        $dropzone = $em->getRepository('InnovaCollecticielBundle:Dropzone')->find($dropzoneId);
        $document = $em->getRepository('InnovaCollecticielBundle:Document')->find($documentId);

        // Récupération de l'utilisateur
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);

        // Valorisation du commentaire
        $notation = new Notation();
        $notation->setUser($user);
        $notation->setDocument($document);
        $notation->setDropzone($dropzone);
        $notation->setNote($note);
        $notation->setCommentText($commentText);
        $notation->setQualityText($qualityText);
        $notation->setRecordOrTransmit($recordOrTransmit);

        // Insertion en base de la notation
        $em->persist($notation);

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

        // Ajout afin d'afficher la partie du code avec "Demande transmise"
        $template = $this->get('templating')->
        render('InnovaCollecticielBundle:Document:documentIsTransmit.html.twig',
                array('document' => $document,
                      'dropzone' => $dropzone,
                      'recordOrTransmitNotation' => 1,
                    )
               );

        // Retour du template actualisé à l'Ajax et non plus du Json.
        return new Response($template);
    }
}
