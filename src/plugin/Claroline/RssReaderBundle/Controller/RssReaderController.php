<?php

namespace Claroline\RssReaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\RssReaderBundle\Form\ConfigType;
use Claroline\RssReaderBundle\Entity\Config;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RssReaderController extends Controller
{
    public function createConfigAction($workspaceId, $isDashboard, $isDefault)
    {
        $form = $this->get('form.factory')->create(new ConfigType(), new Config());
        $form->bindRequest($this->get('request'));
        $em = $this->get('doctrine.orm.entity_manager');

        if ($form->isValid()) {
            $config = $form->getData();
            $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
            $config->setWorkspace($workspace);
            $config->setDashboard($isDashboard);
            $config->setDefault($isDefault);
            $em->persist($config);
            $em->flush();
        } else {
            return $this->renderrender(
                'ClarolineRssReaderBundle::form_workspace_create.html.twig', array(
                'form' => $form->createView(),
                'workspaceId' => $workspaceId,
                'isDashboard' => $isDashboard,
                'isDefault' => $isDefault
                )
            );
        }

        if ($config->getWorkspace() != null) {
            return new RedirectResponse($this->generateUrl('claro_workspace_home', array('workspaceId' => $config->getWorkspace()->getId())));
        } else {
            return new RedirectResponse($this->generateUrl('claro_admin_widgets'));
        }
    }

    public function updateConfigAction($configId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $rssConfig = $em->getRepository('Claroline\RssReaderBundle\Entity\Config')->find($configId);
        $form = $this->get('form.factory')->create(new ConfigType(), $rssConfig);
        $form->bindRequest($this->get('request'));

        if ($form->isValid()) {
            $config = $form->getData();
            $em->persist($config);
            $em->flush();
        } else {
            return $this->renderrender(
                'ClarolineRssReaderBundle::form_workspace_update.html.twig', array(
                'form' => $form->createView(),
                'rssConfig' => $rssConfig
                )
            );
        }

        if ($rssConfig->getWorkspace() != null) {
            return new RedirectResponse($this->generateUrl('claro_workspace_home', array('workspaceId' => $rssConfig->getWorkspace()->getId())));
        } else {
            return new RedirectResponse($this->generateUrl('claro_admin_widgets'));
        }
    }
}
