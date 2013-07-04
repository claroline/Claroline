<?php

namespace Claroline\RssReaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Claroline\RssReaderBundle\Form\ConfigType;
use Claroline\RssReaderBundle\Entity\Config;

class RssReaderController extends Controller
{
    /**
     * @Route(
     *     "/workspace/{workspaceId}/isDesktop/{isDesktop}/isDefault/{isDefault}/user/{userId}",
     *     name="claro_rss_config_create"
     * )
     * @Method("POST")
     */
    public function createConfigAction($workspaceId, $isDesktop, $isDefault, $userId)
    {
        $form = $this->get('form.factory')->create(new ConfigType(), new Config());
        $form->handleRequest($this->get('request'));
        $em = $this->get('doctrine.orm.entity_manager');

        if ($form->isValid()) {
            $config = $form->getData();
            $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
            $user = $em->getRepository('ClarolineCoreBundle:User')->find($userId);
            $config->setWorkspace($workspace);
            $config->setUser($user);
            $config->setDesktop($isDesktop);
            $config->setDefault($isDefault);
            $em->persist($config);
            $em->flush();
        } else {
            if ($workspaceId === 0) {
                $template = 'ClarolineRssReaderBundle::desktopFormCreate.html.twig';
                $workspace = null;
            } else {
                $template = 'ClarolineRssReaderBundle::workspaceFormCreate.html.twig';
                $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
                    ->find($workspaceId);
            }

            return $this->render(
                $template, array(
                'workspace' => $workspace,
                'form' => $form->createView(),
                'workspaceId' => $workspaceId,
                'isDesktop' => $isDesktop,
                'isDefault' => $isDefault,
                'userId' => $userId,
                )
            );

        }

        if ($config->getWorkspace() != null) {
            $url = $this->generateUrl(
                'claro_workspace_open',
                array('workspaceId' => $config->getWorkspace()->getId())
            );

            return new RedirectResponse($url);
        }

        if ($isDefault) {

            return new RedirectResponse($this->generateUrl('claro_admin_widgets'));
        }

        return new RedirectResponse($this->generateUrl('claro_desktop_open'));
    }

    /**
     * @Route(
     *     "/update/workspace/{configId}",
     *     name="claro_rss_config_update"
     * )
     * @Method("POST")
     */
    public function updateConfigAction($configId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $rssConfig = $em->getRepository('ClarolineRssReaderBundle:Config')->find($configId);
        $this->checkAccess($rssConfig);
        $form = $this->get('form.factory')->create(new ConfigType(), $rssConfig);
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $config = $form->getData();
            ($config->getUrl() == '') ? $em->remove($config): $em->persist($config);
            $em->flush();
        } else {
            if ($rssConfig->getWorkspace() === null) {
                $template = 'ClarolineRssReaderBundle::desktopFormUpdate.html.twig';
            } else {
                $template = 'ClarolineRssReaderBundle::workspaceFormUpdate.html.twig';
            }

            return $this->render(
                $template, array(
                'form' => $form->createView(),
                'rssConfig' => $rssConfig,
                'workspace' => $rssConfig->getWorkspace()
                )
            );
        }

        if ($rssConfig->getWorkspace() != null) {
            $url = $this->generateUrl(
                'claro_workspace_open',
                array('workspaceId' => $rssConfig->getWorkspace()->getId())
            );

            return new RedirectResponse($url);
        }

        if (!$rssConfig->isDefault() && $rssConfig->isDesktop()) {
            return new RedirectResponse($this->generateUrl('claro_desktop_open'));
        }

        return new RedirectResponse($this->generateUrl('claro_admin_widgets'));
    }

    private function checkAccess(Config $rssConfig)
    {
        $security = $this->get('security.context');
        $workspace = $rssConfig->getWorkspace();

        if ($workspace !== null && !$security->isGranted('parameters', $workspace)
            || $security->getToken()->getUser() !== $rssConfig->getUser()) {
            throw new AccessDeniedException();
        }
    }
}