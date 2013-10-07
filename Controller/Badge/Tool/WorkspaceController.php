<?php

namespace Claroline\CoreBundle\Controller\Badge\Tool;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeTranslation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/workspace/{workspaceId}/badges")
 */
class WorkspaceController extends Controller
{
    /**
     * @Route("/{page}", name="claro_workspace_tool_badges", requirements={"page" = "\d+"}, defaults={"page" = 1})
     *
     * @Template("ClarolineCoreBundle:Badge/Tool:workspace.html.twig")
     */
    public function listAction($workspaceId, $page)
    {
        /** @var \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace */
        $workspace = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        $badgeClaims = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Badge\BadgeClaim')->findAll();

        $parameters = array(
            'page'         => $page,
            'workspace'    => $workspace,
            'add_link'     => 'claro_workspace_tool_badges_add',
            'edit_link'    => array(
                'url'    => 'claro_admin_badges_edit',
                'suffix' => '#!edit'
            ),
            'delete_link'  => 'claro_admin_badges_delete',
            'view_link'    => 'claro_admin_badges_edit',
            'current_link' => 'claro_workspace_tool_badges',
            'route_parameters' => array(
                'workspaceId' => $workspaceId
            ),
        );

        return array(
            'workspace'   => $workspace,
            'badgeClaims' => $badgeClaims,
            'parameters'  => $parameters
        );
    }

    /**
     * @Route("/add", name="claro_workspace_tool_badges_add")
     *
     * @Template()
     */
    public function addAction(Request $request, $workspaceId)
    {
        /** @var \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace */
        $workspace = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        $badge = new Badge();

        //@TODO Get locales from locale source (database etc...)
        $locales = array('fr', 'en');
        foreach ($locales as $locale) {
            $translation = new BadgeTranslation();
            $translation->setLocale($locale);
            $badge->addTranslation($translation);
        }

        /** @var \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler $platformConfigHandler */
        $platformConfigHandler = $this->get('claroline.config.platform_config_handler');

        $form = $this->createForm($this->get('claroline.form.badge'), $badge, array('language' => $platformConfigHandler->getParameter('locale_language'), 'date_format' => $this->get('translator')->trans('date_form_format', array(), 'platform')));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
                $translator = $this->get('translator');
                try {
                    /** @var \Doctrine\Common\Persistence\ObjectManager $entityManager */
                    $entityManager = $this->getDoctrine()->getManager();

                    $badge->setWorkspace($workspace);

                    $entityManager->persist($badge);
                    $entityManager->flush();

                    $this->get('session')->getFlashBag()->add('success', $translator->trans('badge_add_success_message', array(), 'badge'));
                } catch (\Exception $exception) {
                    $this->get('session')->getFlashBag()->add('error', $translator->trans('badge_add_error_message', array(), 'badge'));
                }

                return $this->redirect($this->generateUrl('claro_admin_badges'));
            }
        }

        return array(
            'workspace' => $workspace,
            'form'      => $form->createView()
        );
    }
}
