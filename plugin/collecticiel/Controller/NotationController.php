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
use Innova\CollecticielBundle\Entity\Drop;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Innova\CollecticielBundle\Event\Log\LogDropzoneValidateDocumentEvent;

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
     *      "/document/{documentId}",
     *      name="innova_collecticiel_validate_transmit_evaluation",
     *      requirements={"documentId" = "\d+", "dropzoneId" = "\d+"},
     *      options={"expose"=true}
     * )
     * @ParamConverter("document", class="InnovaCollecticielBundle:Document", options={"id" = "documentId"})
     * @Template()
     */
    public function ajaxValidateTransmitEvaluationDocumentAction(Document $document)
    {

        // Appel pour accés base         
        $em = $this->getDoctrine()->getManager();

        // Recherche en base des données du document à mettre à jour
        $doc = $this->getDoctrine()->getRepository('InnovaCollecticielBundle:Document')->find($document->getId());

        // Mise à jour du booléen de Validation de false à true
        $doc->setvalidate(true);

        // Récupération du dropID puis du dropZone
        $dropId = $document->getDrop()->getId();

        $dropRepo = $this->getDoctrine()->getRepository('InnovaCollecticielBundle:Drop');
        $drops = $dropRepo->findBy(array('id' => $dropId));

        $dropzoneRepo = $this->getDoctrine()->getRepository('InnovaCollecticielBundle:DropZone');
        $dropzones = $dropzoneRepo->findBy(array('id' => $drops[0]->getDropzone()->getId()));

        // Mise à jour de la base de données
        //$em->persist($doc);
        //$em->flush();

        $dropzoneManager = $this->get('innova.manager.dropzone_manager');
        $collecticielOpenOrNot = $dropzoneManager->collecticielOpenOrNot($dropzones[0]);

        // Envoi notification. InnovaERV
        $usersIds = array();

        // Ici, on récupère le créateur du collecticiel = l'admin
        if ($document->getType() == 'url') {
            $userCreator = $document->getDrop()->getDropzone()->getResourceNode()->getCreator()->getId();
        } else {
            $userCreator = $document->getResourceNode()->getCreator()->getId();
        }

        // Ici, on récupère celui qui vient de déposer le nouveau document
        //$userAddDocument = $this->get('security.context')->getToken()->getUser()->getId(); 
        $userDropDocument = $document->getDrop()->getUser()->getId();
        $userSenderDocument = $document->getSender()->getId();

        if ($userCreator == $userSenderDocument) {
            // Ici avertir l'étudiant qui a travaillé sur ce collecticiel
            $usersIds[] = $userDropDocument;
        } else {
            // Ici avertir celui a qui créé le collecticiel
            $usersIds[] = $userCreator;
        }

        $event = new LogDropzoneValidateDocumentEvent($document, $dropzones[0], $usersIds);

        $this->get('event_dispatcher')->dispatch('log', $event);

        // Ajout afin d'afficher la partie du code avec "Demande transmise"
        $template = $this->get('templating')->
        render('InnovaCollecticielBundle:Document:documentIsValidate.html.twig',
                array('document' => $document,
                      'collecticielOpenOrNot' => $collecticielOpenOrNot,
                      'dropzone' => $dropzones[0],
                    )
               );

        // Retour du template actualisé à l'Ajax et non plus du Json.
        return new Response($template);
    }
}
