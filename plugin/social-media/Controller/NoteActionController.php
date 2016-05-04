<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 5/7/15
 */

namespace Icap\SocialmediaBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Icap\SocialmediaBundle\Entity\NoteAction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NoteActionController extends Controller
{
    /**
     * @Route("/note/form/{resourceId}", name="icap_socialmedia_note_form", )
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     *
     * @param int  $resourceId
     * @param User $user
     *
     * @return array
     */
    public function formAction($resourceId, User $user)
    {
        $noteManager = $this->getNoteActionManager();
        $notesQB = $noteManager->getNotesForPagination($resourceId, $user);
        $pager = $this->paginateQuery($notesQB, 1);

        return array('resourceId' => $resourceId, 'pager' => $pager);
    }

    /**
     * @Route("/note/{resourceId}", name="icap_socialmedia_note")
     * @Method({"POST"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param Request $request
     * @param $resourceId
     * @param User $user
     *
     * @return JsonResponse
     */
    public function noteAction(Request $request, $resourceId, User $user)
    {
        $text = $request->get('social_media_note_text');
        $jsonResponse = new JsonResponse(true);
        if ($text !== null) {
            $note = new NoteAction();
            $note->setUser($user);
            $note->setText($text);
            $this->getNoteActionManager()->createNote($resourceId, $note);
            $jsonResponse = new JsonResponse(true);
        } else {
            $jsonResponse->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return $jsonResponse;
    }

    /**
     * @Route("/note/list/{resourceId}/{page}", name="icap_socialmedia_notelist", defaults={"page" = "1"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     *
     * @param $page
     *
     * @return array
     */
    public function noteListAction($resourceId, $page, User $user)
    {
        $notesQB = $this->getNoteActionManager()->getNotesForPagination($resourceId, $user);
        $pager = $this->paginateQuery($notesQB, $page);

        return array('pager' => $pager, 'resourceId' => $resourceId);
    }

    /**
     * @Route("/note/item/{id}", name="icap_socialmedia_note_delete", requirements={"id" : "\d+"})
     * @Method({"DELETE"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param $id
     * @param User $user
     *
     * @return JsonResponse
     */
    public function deleteWallItemAction($id, User $user)
    {
        $this->getNoteActionManager()->removeNote($id, $user);
        $response = new JsonResponse(true);

        return $response;
    }
}
