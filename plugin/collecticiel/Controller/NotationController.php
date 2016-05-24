<?php
/**
 * Created by : Eric VINCENT
 * Date: 05/16.
 */

namespace Innova\CollecticielBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Entity\Notation;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    public function AddNotationForDocsInnovaAction()
    {
        // Récupération de l'ID du document
        $documentId = $this->get('request')->query->get('documentId');
        $dropzoneId = $this->get('request')->query->get('dropzoneId');
        $note = $this->get('request')->query->get('note');
        $commentText = $this->get('request')->query->get('commentText');
        $qualityText = $this->get('request')->query->get('qualityText');

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

        // Insertion en base du commentaire
        $em->persist($notation);

        $em->flush();

        $url = $this->generateUrl('innova_collecticiel_drops_awaiting', array(
                    'resourceId' => $dropzone->getId(),
                )
        );

        return new JsonResponse(array('link' => $url));
    }
}
