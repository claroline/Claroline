<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * TextManager will redirect to this controller once a directory is "open" or "edit".
 * This is more or less a test because it's hard to keep the diff between 2 html files and doesn't really
 * work properly for now. It's also untested.
 */
class TextController extends Controller
{
    /**
     * @Route(
     *     "/form/edit/{text}",
     *     name="claro_text_edit_form"
     * )
     *
     * @Template()
     *
     * Displays the text edition form.
     *
     * @param int $textId
     *
     * @return Response
     */
    public function editFormAction(Text $text)
    {
        $collection = new ResourceCollection(array($text->getResourceNode()));
        $this->checkAccess('EDIT', $collection);

        $em = $this->container->get('doctrine.orm.entity_manager');
        $revisionRepo = $em->getRepository('ClarolineCoreBundle:Resource\Revision');

        return array(
            'text' => $revisionRepo->getLastRevision($text)->getContent(),
            '_resource' => $text,
        );
    }

    /**
     * @Route(
     *     "/edit/{old}",
     *     name="claro_text_edit"
     * )
     *
     * Handles the text edition form submission.
     *
     * @param int $textId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Text $old)
    {
        $collection = new ResourceCollection(array($old->getResourceNode()));
        $this->checkAccess('EDIT', $collection);

        $request = $this->get('request');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $text = $request->request->get('content');
        $em = $this->getDoctrine()->getManager();
        $version = $old->getVersion();
        $revision = new Revision();
        $revision->setContent($text);
        $revision->setText($old);
        $revision->setVersion(++$version);
        $revision->setUser($user);
        $em->persist($revision);
        $old->setVersion($version);
        $em->flush();
        $workspace = $old->getResourceNode()->getWorkspace();
        $usersToNotify = $workspace ?
            $this->container->get('claroline.manager.user_manager')
                ->getUsersByWorkspaces(array($workspace), null, null, false) :
            array();

        $this->get('claroline.event.event_dispatcher')
            ->dispatch(
                'log',
                'Log\LogEditResourceText',
                array('node' => $old->getResourceNode(), 'usersToNotify' => $usersToNotify)
            );

        $route = $this->get('router')->generate(
            'claro_resource_open',
            array('resourceType' => 'text', 'node' => $old->getResourceNode()->getId())
        );

        return new RedirectResponse($route);
    }

    /**
     * @Route(
     *     "/open/{text}",
     *     name="claro_text_open"
     * )
     *
     * Handles the text edition form submission.
     *
     * @param int $textId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function openAction(Text $text)
    {
        $revisionRepo = $this->getDoctrine()->getManager()
            ->getRepository('ClarolineCoreBundle:Resource\Revision');
        $collection = new ResourceCollection(array($text->getResourceNode()));
        $isGranted = $this->container->get('security.authorization_checker')->isGranted('EDIT', $collection);

        return $this->render(
            'ClarolineCoreBundle:Text:index.html.twig',
            array(
                'text' => $revisionRepo->getLastRevision($text)->getContent(),
                '_resource' => $text,
                'isEditGranted' => $isGranted,
            )
        );
    }

    /**
     * Checks if the current user has the right to perform an action on a ResourceCollection.
     * Be careful, ResourceCollection may need some aditionnal parameters.
     *
     * - for CREATE: $collection->setAttributes(array('type' => $resourceType))
     *  where $resourceType is the name of the resource type.
     * - for MOVE / COPY $collection->setAttributes(array('parent' => $parent))
     *  where $parent is the new parent entity.
     *
     * @param string             $permission
     * @param ResourceCollection $collection
     *
     * @throws AccessDeniedException
     */
    public function checkAccess($permission, ResourceCollection $collection)
    {
        if (!$this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
