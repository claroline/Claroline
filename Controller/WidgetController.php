<?php

namespace Icap\BlogBundle\Controller;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

use Doctrine\ORM\EntityManager;
use Icap\BlogBundle\Entity\WidgetList;
use Icap\BlogBundle\Form\WidgetListType;
use Icap\BlogBundle\Listener\BlogListener;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WidgetController extends Controller
{
    /**
     * @Route("/icap_blog/widget/{id}/config", name="icap_blog_widget_list_configure", requirements={"id" = "\d+"})
     * @Method("POST")
     */
    public function updateWidgetBlogList(Request $request, WidgetInstance $widgetInstance)
    {
        if (!$this->get('security.context')->isGranted('edit', $widgetInstance)) {
            throw new AccessDeniedException();
        }

        $widgetList = new WidgetList();
        $widgetList->setWidgetInstance($widgetInstance);

        /** @var Form $form */
        $form = $this->container->get('form.factory')->create($this->get('icap_blog.form.widget_list'), $widgetList);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager = $this->get('doctrine.orm.entity_manager');
            $entityManager->persist($widgetList);
            $entityManager->flush();

            return new Response('', Response::HTTP_NO_CONTENT);
        } else {
            $widgetItems = $this->get('icap_blog.manager.widget')->getWidgetList($widgetInstance);

            return $this->render(
                'IcapBlogBundle:widget:listConfigure.html.twig',
                array(
                    'form'           => $form->createView(),
                    'widgetInstance' => $widgetInstance,
                    'widgetItems'    => $widgetItems
                )
            );
        }
    }
}
